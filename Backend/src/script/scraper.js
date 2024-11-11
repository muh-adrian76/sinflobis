// scraper.js
require("dotenv").config();
const puppeteer = require("puppeteer");
const mysql = require("mysql2/promise");

const scrapeGoogleMaps = async (query) => {
  const browser = await puppeteer.launch({ headless: true });
  const page = await browser.newPage();
  await page.setUserAgent(
    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/85.0.4183.121 Safari/537.36"
  );
  const url = "https://www.google.com/maps/";

  await page.goto(url, {
    waitUntil: "networkidle2",
  });

  await page.waitForSelector(".searchboxinput", { timeout: 10000 });
  await page.type(".searchboxinput", query);
  await page.waitForSelector('div[data-index="0"]', { timeout: 10000 });
  const firstSuggestion = await page.$('div[data-index="0"]');
  if (firstSuggestion) {
    await firstSuggestion.click();
  } else {
    throw new Error("No autocomplete suggestions found.");
  }

  await page.waitForSelector('div[role="main"]', {
    timeout: 10000,
  });
  const jamSibuk = await page.waitForSelector(
    'div[aria-label^="Jam favorit di"]',
    {
      timeout: 10000,
    }
  );
  if (!jamSibuk) {
    try {
      // If the popular times div isn't available, search for div[role="feed"] and click the first link
      const feedDiv = await page.waitForSelector('div[role="feed"]', {
        timeout: 10000, // Shorter timeout for fallback
      });

      if (feedDiv) {
        const firstLink = await feedDiv.$("a.hfpxzc");
        if (firstLink) {
          await firstLink.click();
          await page.waitForSelector('div[role="main"]', {
            timeout: 10000,
          });
          await page.waitForSelector('div[aria-label^="Jam favorit di"]', {
            timeout: 10000,
          });
        } else {
          throw new Error("No link found in div[role='feed']");
        }
      } else {
        throw new Error("div[role='feed'] not found");
      }
    } catch (secondaryError) {
      // If popular times data and the alternative feed div are not available, return a fallback message
      return {
        popularTimesData: `${query} : <strong>tidak ada data jam sibuk</strong>`,
      };
    }
  }

  const locationData = await page.evaluate(() => {
    const name = document.querySelector("h1").textContent;
    const url = window.location.href;

    // Use a regular expression to match the "@latitude,longitude" pattern
    const match = url.match(/@(-?\d+\.\d+),(-?\d+\.\d+)/);
    if (match) {
      const latitude = match[1];
      const longitude = match[2];

      return { name, latitude, longitude };
    }
  });

  const popularTimesData = await page.evaluate(() => {
    const data = [];
    const days = [
      "Minggu",
      "Senin",
      "Selasa",
      "Rabu",
      "Kamis",
      "Jumat",
      "Sabtu",
    ];
    const dayElements = document.querySelectorAll(
      'div[aria-label^="Jam favorit"]'
    );
    let currentDayIndex = 0;
    dayElements.forEach((dayElement) => {
      const hourElements = dayElement.querySelectorAll('div[role="img"]');
      hourElements.forEach((hourElement, index) => {
        const ariaLabel = hourElement.getAttribute("aria-label");

        // Updated regex to match both active and inactive states
        const hourMatchActive = ariaLabel.match(/Saat ini (\d+)% ramai/);
        const hourMatchInactive = ariaLabel.match(
          /(\d+)% ramai pada pukul (\d{1,2}\.\d{2})/
        );

        let busyPercentage;
        let time;

        if (hourMatchActive) {
          busyPercentage = parseInt(hourMatchActive[1], 10);
          const calculatedHour = (index + 4) % 24; // Start from 4:00 AM
          time = `${calculatedHour.toString().padStart(2, "0")}.00`;
        } else if (hourMatchInactive) {
          busyPercentage = parseInt(hourMatchInactive[1], 10);
          time = hourMatchInactive[2];
        }

        if (busyPercentage !== undefined && busyPercentage !== 0) {
          data.push({
            day: days[currentDayIndex],
            time,
            busy_percentage: busyPercentage,
          });
        }
        if ((index + 1) % 24 === 0) {
          currentDayIndex = (currentDayIndex + 1) % days.length; // Loop back to 0 if it exceeds 6 (Saturday)
        }
      });
    });
    return data;
  });

  await browser.close();
  return { locationData, popularTimesData };
};

const saveToDatabase = async (locationData, popularTimesData) => {
  const connection = await mysql.createConnection({
    user: process.env.MYSQL_USER,
    host: process.env.MYSQL_HOST,
    database: process.env.MYSQL_DATABASE,
    password: process.env.MYSQL_PASSWORD,
    port: process.env.MYSQL_PORT,
  });

  try {
    await connection.beginTransaction();

    // const [locationChecks] = await connection.execute(
    //   "SELECT * FROM locations WHERE name = ?",
    //   [locationData.name]
    // );
    // if (locationChecks.length > 0) {
    //   throw new Error("Lokasi sudah ada di database");
    // }

    locationData.name = locationData.name.replace(/"/g, "");
    const [locationResult] = await connection.execute(
      "INSERT INTO locations (name, latitude, longitude) VALUES (?, ?, ?)",
      [locationData.name, locationData.latitude, locationData.longitude]
    );
    const locationId = locationResult.insertId;

    for (const dayData of popularTimesData) {
      const hour = parseInt(dayData.time, 10);
      const formattedTime = `${hour.toString().padStart(2, "0")}:00`;

      await connection.execute(
        "INSERT INTO popular_times (day, time, busy_percentage, location_id) VALUES (?, ?, ?, ?)",
        [dayData.day, formattedTime, dayData.busy_percentage, locationId]
      );
    }

    // Commit the transaction
    await connection.commit();
    console.log(`Berhasil menyimpan data ${locationData.name} pada database!`);
  } catch (error) {
    // Roll back the transaction on error
    await connection.rollback();
  } finally {
    // Close the connection
    await connection.end();
  }
};

module.exports = { scrapeGoogleMaps, saveToDatabase };

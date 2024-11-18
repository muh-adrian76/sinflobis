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

  await page.setViewport({ width: 1600, height: 900 });
  await page.goto(url, {
    waitUntil: "networkidle2",
  });

  const delay = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

  await page.waitForSelector(".searchboxinput", { timeout: 30000 });
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
  await delay(5000);
  let jamSibuk;
  try {
    jamSibuk = await page.waitForSelector('div[aria-label^="Jam favorit di"]', {
      timeout: 10000,
    });
  } catch (error) {
    // Handle the error when the selector is not found
    // console.error("jamSibuk selector not found:", error);
  }
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
          await delay(5000);
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
    const match = url.match(/@(-?\d+\.\d+),(-?\d+\.\d+)/);
    if (match) {
      const latitude = match[1];
      let longitude = match[2];
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

const createGroup = async (group_name) => {
  const connection = await mysql.createConnection({
    user: process.env.MYSQL_USER,
    host: process.env.MYSQL_HOST,
    database: process.env.MYSQL_DATABASE,
    password: process.env.MYSQL_PASSWORD,
    port: process.env.MYSQL_PORT,
  });
  try {
    await connection.beginTransaction();
    let groupId;

    if (group_name === "") {
      throw new Error(`Invalid value`);
    }

    if (group_name !== "") {
      const [existingGroup] = await connection.execute(
        "SELECT id FROM location_groups WHERE name = ?",
        [group_name]
      );

      if (existingGroup.length > 0) {
        groupId = existingGroup[0].id;
        console.log(`Grup ${group_name} sudah ada dengan ID: ${groupId}`);
      } else {
        const [insertResult] = await connection.execute(
          "INSERT INTO location_groups (name) VALUES (?)",
          [group_name]
        );
        groupId = insertResult.insertId;
        console.log(`Berhasil menambahkan grup ${group_name} pada database!`);
      }
    }
    await connection.commit();
    return groupId;
  } catch (error) {
    console.log(`Gagal menambahkan grup ${group_name} pada database!`);
    await connection.rollback();
  } finally {
    // Close the connection
    await connection.end();
  }
};

const rescrapeSelection = async (query, groupId) => {
  const connection = await mysql.createConnection({
    user: process.env.MYSQL_USER,
    host: process.env.MYSQL_HOST,
    database: process.env.MYSQL_DATABASE,
    password: process.env.MYSQL_PASSWORD,
    port: process.env.MYSQL_PORT,
  });

  try {
    let queries = [];
    if (groupId) {
      const [locations] = await connection.execute(
        "SELECT name FROM locations WHERE grup = ?",
        [groupId]
      );

      // Extract location names
      queries = locations.map((location) => location.name);
    }

    if (query) {
      if (!queries.includes(query)) {
        queries.push(query);
      }
    }

    return queries;
  } catch (error) {
    console.error("Error fetching queries:", error);
    throw new Error("Could not fetch queries from the database");
  } finally {
    await connection.end();
  }
};

const saveToDatabase = async (locationData, groupId, popularTimesData) => {
  const connection = await mysql.createConnection({
    user: process.env.MYSQL_USER,
    host: process.env.MYSQL_HOST,
    database: process.env.MYSQL_DATABASE,
    password: process.env.MYSQL_PASSWORD,
    port: process.env.MYSQL_PORT,
  });
  try {
    await connection.beginTransaction();

    // remove ""
    locationData.name = locationData.name.replace(/"/g, "");
    const [locationChecks] = await connection.execute(
      "SELECT id FROM locations WHERE name = ?",
      [locationData.name]
    );

    let locationId;

    if (locationChecks.length > 0) {
      // Location exists, update the existing record
      locationId = locationChecks[0].id; // Get the existing location ID
      await connection.execute(
        "UPDATE locations SET last_updated=NOW() WHERE id = ?",
        [locationId]
      );
      await connection.execute(
        "DELETE FROM popular_times WHERE location_id = ?",
        [locationId]
      );
      await connection.execute("ALTER TABLE popular_times DROP id;");
      await connection.execute(
        "ALTER TABLE popular_times ADD id INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;"
      );
      for (const dayData of popularTimesData) {
        const hour = parseInt(dayData.time, 10);
        const formattedTime = `${hour.toString().padStart(2, "0")}:00`;

        await connection.execute(
          "INSERT INTO popular_times (day, time, busy_percentage, location_id) VALUES (?, ?, ?, ?)",
          [dayData.day, formattedTime, dayData.busy_percentage, locationId]
        );
      }
      console.log(
        `Berhasil memperbarui data ${locationData.name} pada database!`
      );
    } else {
      const [locationResult] = await connection.execute(
        "INSERT INTO locations (name, grup, latitude, longitude, last_updated) VALUES (?, ?, ?, ?, NOW())",
        [
          locationData.name,
          groupId,
          locationData.latitude,
          locationData.longitude,
        ]
      );
      locationId = locationResult.insertId; // Get the new location ID
      for (const dayData of popularTimesData) {
        const hour = parseInt(dayData.time, 10);
        const formattedTime = `${hour.toString().padStart(2, "0")}:00`;

        await connection.execute(
          "INSERT INTO popular_times (day, time, busy_percentage, location_id) VALUES (?, ?, ?, ?)",
          [dayData.day, formattedTime, dayData.busy_percentage, locationId]
        );
      }
      console.log(
        `Berhasil menyimpan data ${locationData.name} pada database!`
      );
    }

    await connection.commit();
  } catch (error) {
    // Roll back the transaction on error
    console.error(error);
    console.log(`Gagal menyimpan data ${locationData.name} pada database!`);

    await connection.rollback();
  } finally {
    // Close the connection
    await connection.end();
  }
};

module.exports = {
  scrapeGoogleMaps,
  createGroup,
  rescrapeSelection,
  saveToDatabase,
};

require("dotenv").config();
const puppeteer = require("puppeteer");
const mysql = require("mysql2/promise");
const fs = require("fs");
const path = require("path");

const findCoordinate = async (location) => {
  const connection = await mysql.createConnection({
    user: process.env.MYSQL_USER,
    host: process.env.MYSQL_HOST,
    database: process.env.MYSQL_DATABASE,
    password: process.env.MYSQL_PASSWORD,
    port: process.env.MYSQL_PORT,
  });
  try {
    await connection.beginTransaction();

    if (location === "") {
      throw new Error(`Invalid value`);
    }
    const [result] = await connection.execute(
      "SELECT latitude, longitude FROM locations WHERE name = ?",
      [location]
    );
    const latitude = result[0].latitude;
    const longitude = result[0].longitude;
    await connection.commit();
    return { latitude, longitude };
  } catch (error) {
    console.log(
      `Gagal mencari koordinat dari lokasi ${location} pada database!`
    );
    await connection.rollback();
  } finally {
    // Close the connection
    await connection.end();
  }
};

const takeScreenshot = async (
  nama,
  latitude,
  longitude,
  hari = false,
  waktu = false,
  zoom = false
) => {
  const browser = await puppeteer.launch({ headless: true });
  const page = await browser.newPage();
  await page.setUserAgent(
    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/85.0.4183.121 Safari/537.36"
  );

  await page.setViewport({ width: 1600, height: 900 });
  const delay = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

  const adjustedLongitude = parseFloat(longitude) + 0.01;
  let url;
  if (nama !== "manual") {
    url = `https://www.google.com/maps/@${latitude},${adjustedLongitude},1000m`;
  } else {
    url = `https://www.google.com/maps/@${latitude},${longitude},${zoom}m`;
  }
  await page.goto(url, { waitUntil: "networkidle2" });

  await page.waitForSelector("button.yHc72", {
    timeout: 30000,
  });
  const layer = await page.$("button.yHc72");
  if (layer) {
    await layer.click(); // Click the traffic button
  } else {
    throw new Error("No layer found.");
  }

  await page.waitForSelector("div.uSxocb", {
    timeout: 15000,
  });
  const traffic = await page.$("div.uSxocb");
  if (traffic) {
    await traffic.click(); // Click the traffic button
  } else {
    throw new Error("No traffic found.");
  }

  await delay(5000);

  if (hari && waktu) {
    await page.waitForSelector('div[role="option"]', {
      timeout: 15000,
    });
    const liveTraffic = await page.$('div[role="option"]');
    if (liveTraffic) {
      await liveTraffic.click();
    } else {
      throw new Error("No typical traffic found.");
    }

    await page.waitForSelector('div[id=":1"]', {
      timeout: 10000,
    });
    const typicalTraffic = await page.$('div[id=":1"]');
    if (typicalTraffic) {
      await typicalTraffic.click();
    } else {
      throw new Error("No typical traffic found.");
    }

    // days
    await page.waitForSelector(`button[aria-label="${hari}"]`, {
      timeout: 10000,
    });
    const day = await page.$(`button[aria-label="${hari}"]`);
    if (day) {
      await day.click();
    } else {
      throw new Error("Days not found.");
    }

    // time
    await page.waitForSelector(".BG6pXb", {
      timeout: 10000,
    });
    const slider = await page.$(".BG6pXb");
    const ariaValueMin = await slider.evaluate((el) =>
      el.getAttribute("aria-valuemin")
    );
    const ariaValueMax = await slider.evaluate((el) =>
      el.getAttribute("aria-valuemax")
    );
    const ariaValueText = await slider.evaluate((el) =>
      el.getAttribute("aria-valuetext")
    );
    const timeToMinutesPastStart = (time) => {
      const [hours, minutes] = time.split(":").map(Number);
      return (hours - 6) * 60 + minutes;
    };
    const boundingBox = await slider.boundingBox();
    if (boundingBox) {
      const minSliderValue = 827;
      const maxSliderValue = 1005;
      const sliderStartTime = 6 * 60; // 6:00 AM in minutes
      const sliderEndTime = 22 * 60; // 10:00 PM in minutes
      const sliderRange = maxSliderValue - minSliderValue;

      // Calculate step size per minute
      const stepSize = sliderRange / (sliderEndTime - sliderStartTime);

      // Calculate target slider value
      const minutesPastStart = timeToMinutesPastStart(waktu);
      const targetX = minSliderValue + stepSize * minutesPastStart;
      const targetY = boundingBox.y + boundingBox.height / 2; // Middle of the slider

      // console.log(`Waktu: ${minutesPastStart}, Lokasi pada slider: ${targetX}`);

      // Drag the slider
      await page.mouse.move(boundingBox.x, targetY); // Move to slider
      await page.mouse.down(); // Start drag
      await page.mouse.move(targetX - 3.5, targetY, { steps: 10 }); // Drag to target position
      await page.mouse.up(); // Release drag
    } else {
      console.error("Bounding box not found for the slider");
    }
  }

  const folderPath = path.join(__dirname, "screenshots");
  if (!fs.existsSync(folderPath)) {
    fs.mkdirSync(folderPath);
  }

  let screenshotPath;
  // Take a screenshot
  if (hari && waktu) {
    screenshotPath = path.join(
      folderPath,
      `screenshot-${nama}-${hari}-${waktu.replace(":", ".")}.png`
    );
  } else {
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, "0"); // Months are zero-based
    const day = String(now.getDate()).padStart(2, "0");
    const hours = String(now.getHours()).padStart(2, "0");
    const minutes = String(now.getMinutes()).padStart(2, "0");
    const seconds = String(now.getSeconds()).padStart(2, "0");

    // Format the current time as a string
    const currentTime = `${hours}-${minutes}-${seconds}-WIB-${day}-${month}-${year}`;

    screenshotPath = path.join(
      folderPath,
      `screenshot-${nama}-live-${currentTime}.png`
    );
  }
  await page.screenshot({ path: screenshotPath, fullPage: true });
  await browser.close();
  return screenshotPath;
};

const filterGoogleMapsUrl = async (url) => {
  const match = url.match(/@(-?\d+\.\d+),(-?\d+\.\d+),(\d+)m/);
  if (match) {
    const latitude = parseFloat(match[1]);
    const longitude = parseFloat(match[2]);
    const zoom = parseInt(match[3], 10);

    return {
      latitude,
      longitude,
      zoom,
    };
  } else {
    return {
      latitude: null,
      longitude: null,
      zoom: null,
    };
  }
};

module.exports = { findCoordinate, takeScreenshot, filterGoogleMapsUrl };

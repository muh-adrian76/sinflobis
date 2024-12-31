require("dotenv").config();
const puppeteer = require("puppeteer");
const mysql = require("mysql2/promise");
const fs = require("fs");
const path = require("path");

const delay = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

function newTimestamp() {
  const now = new Date();
  const year = now.getFullYear();
  const month = String(now.getMonth() + 1).padStart(2, "0"); // Months are zero-based
  const day = String(now.getDate()).padStart(2, "0");
  const hours = String(now.getHours()).padStart(2, "0");
  const minutes = String(now.getMinutes()).padStart(2, "0");
  const seconds = String(now.getSeconds()).padStart(2, "0");
  return { year, month, day, hours, minutes, seconds };
}

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
  zoom = false,
  zoomType = false
) => {
  const browser = await puppeteer.launch({ headless: true });
  const page = await browser.newPage();
  await page.setUserAgent(
    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/85.0.4183.121 Safari/537.36"
  );

  await page.setViewport({ width: 1600, height: 900 });

  const adjustedLongitude = parseFloat(longitude) + 0.01;
  let url;
  if (nama !== "manual") {
    url = `https://www.google.com/maps/@${latitude},${adjustedLongitude},1500m`;
  } else {
    url =
      zoomType === "z"
        ? `https://www.google.com/maps/@${latitude},${longitude},${zoom}z`
        : `https://www.google.com/maps/@${latitude},${longitude},${zoom}m`;
  }
  await page.goto(url, { waitUntil: "networkidle2" });

  await page.waitForSelector("button.yHc72", {
    timeout: 60000,
  });
  const layer = await page.$("button.yHc72");
  if (layer) {
    await layer.click(); // Click the traffic button
  } else {
    throw new Error("No layer found.");
  }

  await page.waitForSelector("div.uSxocb", {
    timeout: 30000,
  });
  const traffic = await page.$("div.uSxocb");
  if (traffic) {
    await traffic.click(); // Click the traffic button
    await layer.click();
  } else {
    throw new Error("No traffic found.");
  }

  await delay(5000);

  if (hari && waktu) {
    await page.waitForSelector('div[role="option"]', {
      timeout: 30000,
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
  let currentTime;
  const { year, month, day, hours, minutes, seconds } = newTimestamp();
  const timestamp = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
  // Take a screenshot
  let fileName;
  if (hari && waktu) {
    currentTime = `WIB-diambil-${hours}-${minutes}-${seconds}-WIB-${day}-${month}-${year}`;
    fileName = `screenshot-${nama.replace(/\s+/g, "-")}-${hari}-${waktu.replace(
      ":",
      "."
    )}-${currentTime}.png`;
    screenshotPath = path.join(folderPath, fileName);
  } else {
    currentTime = `${hours}-${minutes}-${seconds}-WIB-${day}-${month}-${year}`;
    fileName = `screenshot-${nama.replace(
      /\s+/g,
      "-"
    )}-live-${currentTime}.png`;
    screenshotPath = path.join(folderPath, fileName);
  }
  // remove unwanted menu
  const hamburger = await page.$('span[jstcache="29"]');
  if (hamburger) {
    await hamburger.click(); // Click the traffic button
    await delay(5000);
    const sidebar = await page.$('button[aria-label="Tampilkan sidebar"]');
    if (sidebar) {
      await sidebar.click(); // Click the traffic button
      await delay(3000);
      const close = await page.$("button.UHOsgd");
      if (close) await close.click();
    } else {
      throw new Error("No sidebar found.");
    }
  }
  await page.evaluate(() => {
    // Hilangkan popup, toolbar, dan elemen lain yang mengganggu
    const elementsToHide = [
      '[id="omnibox-container"]',
      '[id="gb"]',
      '[id="minimap"]',
      '[class="ZPyEwf"]',
      '[class*="VaFJzb"]',
      '[class="fp2VUc"]',
      '[class*="app-horizontal-widget-holder"]',
      '[class*="app-vertical-widget-holder"]',
      '[class*="scene-footer-container"]',
    ];
    elementsToHide.forEach((selector) => {
      const element = document.querySelector(selector);
      if (element) element.style.display = "none";
    });
  });
  await delay(5000);
  await page.screenshot({ path: screenshotPath, fullPage: true });

  await page.evaluate(() => {
    caches.keys().then((cacheNames) => {
      cacheNames.forEach((cacheName) => {
        caches.delete(cacheName);
      });
    });
  });
  await browser.close();
  return { timestamp, fileName, screenshotPath };
};

const filterGoogleMapsUrl = async (url) => {
  try {
    const match = url.match(/@(-?\d+\.\d+),(-?\d+\.\d+),(\d+(\.\d+)?)z/);
    if (match) {
      const latitude = parseFloat(match[1]);
      const longitude = parseFloat(match[2]);
      const zoom = parseInt(match[3], 10);
      const zoomType = "z";
      return { latitude, longitude, zoom, zoomType };
    } else {
      throw new Error("Format zoom 'z' tidak ditemukan");
    }
  } catch (error) {
    const match = url.match(/@(-?\d+\.\d+),(-?\d+\.\d+),(\d+?)m/);
    if (match) {
      const latitude = parseFloat(match[1]);
      const longitude = parseFloat(match[2]);
      const zoom = parseFloat(match[3]);
      const zoomType = "m";
      return { latitude, longitude, zoom, zoomType };
    } else {
      return { latitude: null, longitude: null, zoom: null, zoomType: null };
    }
  }
};

const saveScreenshot = async (
  timestamps,
  jenis,
  nama,
  hari = false,
  waktu = false,
  url,
  koordinat = null
) => {
  const connection = await mysql.createConnection({
    user: process.env.MYSQL_USER,
    host: process.env.MYSQL_HOST,
    database: process.env.MYSQL_DATABASE,
    password: process.env.MYSQL_PASSWORD,
    port: process.env.MYSQL_PORT,
  });
  try {
    await connection.beginTransaction();

    const [pictureChecks] = await connection.execute(
      "SELECT id, url FROM pictures WHERE jenis = ? AND nama = ? AND hari = ? AND waktu = ?",
      [jenis, nama, hari, waktu]
    );

    if (pictureChecks.length > 0) {
      const pictureId = pictureChecks[0].id;
      const pictureUrl = pictureChecks[0].url;
      // delete existing picture
      fs.unlink(pictureUrl, (err) => {
        if (err) {
          console.error(`Error deleting file: ${err}`);
          return;
        }
      });
      await connection.execute(
        "UPDATE pictures SET timestamp = ?, url= ?, area = ? WHERE id = ?",
        [timestamps, url, koordinat, pictureId]
      );
      console.log(
        `Berhasil memperbarui gambar ${nama} pada database, ${timestamps}!`
      );
    } else {
      await connection.execute(
        "INSERT INTO pictures (timestamp, jenis, nama, hari, waktu, url, area) VALUES (?, ?, ?, ?, ?, ?, ?)",
        [timestamps, jenis, nama, hari, waktu, url, koordinat]
      );
      console.log(
        `Berhasil menyimpan gambar ${nama} pada database, ${timestamps}!`
      );
    }

    await connection.commit();
  } catch (error) {
    // Roll back the transaction on error
    console.error(error);
    console.log(`Gagal menyimpan gambar ${nama} pada database!`);

    await connection.rollback();
  } finally {
    // Close the connection
    await connection.end();
  }
};

function calculateCenterPosition(bbox) {
  const [topLeftX, topLeftY, bottomRightX, bottomRightY] = bbox;
  const centerX = (topLeftX + bottomRightX) / 2;
  const centerY = (topLeftY + bottomRightY) / 2;
  return { centerX, centerY };
}

const extractCoordinate = async (url, bbox) => {
  const browser = await puppeteer.launch({ headless: true });
  const page = await browser.newPage();
  await page.setUserAgent(
    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/85.0.4183.121 Safari/537.36"
  );

  await page.setViewport({ width: 1600, height: 900 });

  await page.goto(url, { waitUntil: "networkidle2" });

  const coordinatesList = [];
  for (const entry of bbox) {
    const { position } = entry;
    const { centerX, centerY } = calculateCenterPosition(position);
    await delay(5000);
    await page.mouse.move(centerX, centerY);
    await page.mouse.down({ button: "right" });
    await page.mouse.up({ button: "right" });

    await page.waitForSelector(".mLuXec", {
      timeout: 10000,
    });
    const koordinat = await page.$eval(
      ".mLuXec",
      (element) => element.innerText
    );
    coordinatesList.push({
      rank: entry.rank,
      koordinat: koordinat,
    });
  }

  await browser.close();
  return coordinatesList;
};

module.exports = {
  findCoordinate,
  takeScreenshot,
  filterGoogleMapsUrl,
  saveScreenshot,
  extractCoordinate,
};

require("dotenv").config();
const Hapi = require("@hapi/hapi");
const Inert = require("@hapi/inert");
const path = require("path");
const { exec } = require("child_process");

const ClientError = require("./exceptions/ClientError");
const {
  scrapeGoogleMaps,
  createGroup,
  rescrapeSelection,
  saveScrape,
} = require("./script/scraper");
const {
  takeScreenshot,
  findCoordinate,
  filterGoogleMapsUrl,
  saveScreenshot,
} = require("./script/screenshoot");

const init = async () => {
  const server = Hapi.server({
    port: process.env.PORT,
    host: process.env.HOST,
    routes: {
      cors: {
        origin: ["*"],
      },
    },
  });

  await server.register(Inert);

  server.route({
    method: "GET",
    path: "/",
    handler: (request, h) => {
      return h.redirect("http://localhost:80/disertasi/sinflobis/frontend");
    },
  });

  server.route({
    method: "POST",
    path: "/scrapes",
    handler: async (request, h) => {
      const { query, group, checkbox } = request.payload;
      let groupId;
      if (group !== "") {
        groupId = await createGroup(group);
      } else {
        groupId = "";
      }
      const normalizedQuery = query.replace(/[;,]/g, "\n");
      const queries = normalizedQuery
        .split("\n")
        .map((q) => q.trim())
        .filter((q) => q);
      const results = [];

      for (const singleQuery of queries) {
        const { locationData, popularTimesData } = await scrapeGoogleMaps(
          singleQuery
        );
        if (
          popularTimesData !==
          `${singleQuery} : <strong>tidak ada data jam sibuk</strong>`
        ) {
          if (
            Array.isArray(popularTimesData) &&
            popularTimesData.length === 168
          ) {
            await saveScrape(locationData, groupId, popularTimesData);
            results.push({
              berhasil: `${singleQuery} : <strong>tidak terdapat <i>missing values</i></strong>`,
              locationData,
              popularTimesData,
            });
          } else {
            missingValues = 168 - popularTimesData.length;
            if (checkbox) {
              await saveScrape(locationData, groupId, popularTimesData);
              results.push({
                berhasil: `${singleQuery} : <strong>terdapat ${missingValues} <i>missing values</i></strong>`,
                locationData,
                popularTimesData,
              });
            } else {
              results.push({
                gagal: `${singleQuery} : <strong>terdapat ${missingValues} <i>missing values</i></strong>`,
                popularTimesData,
              });
            }
          }
        } else {
          results.push({ gagal: popularTimesData });
        }
      }
      console.log("Scraping selesai!");
      return h.response({ status: "success", data: results }).code(201);
    },
  });

  server.route({
    method: "POST",
    path: "/rescrapes",
    handler: async (request, h) => {
      const { query, group } = request.payload;
      const normalizedQuery = query.replace(/[;,]/g, "\n");
      const newQuery = normalizedQuery
        .split("\n")
        .map((q) => q.trim())
        .filter((q) => q);
      const groupId = await createGroup(group);
      const results = [];

      // console.log(newQuery, groupId);
      const queries = await rescrapeSelection(newQuery, groupId);
      // console.log(queries);
      for (const singleQuery of queries) {
        const { locationData, popularTimesData } = await scrapeGoogleMaps(
          singleQuery
        );
        if (
          popularTimesData !==
          `${singleQuery} : <strong>tidak ada data jam sibuk</strong>`
        ) {
          if (
            Array.isArray(popularTimesData) &&
            popularTimesData.length === 168
          ) {
            await saveScrape(locationData, groupId, popularTimesData);
            results.push({
              berhasil: `${singleQuery} : <strong>tidak terdapat <i>missing values</i></strong>`,
              locationData,
              popularTimesData,
            });
          } else {
            missingValues = 168 - popularTimesData.length;
            // if (checkbox) {
            await saveScrape(locationData, groupId, popularTimesData);
            results.push({
              berhasil: `${singleQuery} : <strong>terdapat ${missingValues} <i>missing values</i></strong>`,
              locationData,
              popularTimesData,
            });
            // } else {
            //   results.push({
            //     gagal: `${singleQuery} : <strong>terdapat ${missingValues} <i>missing values</i></strong>`,
            //     popularTimesData,
            //   });
            // }
          }
        } else {
          results.push({ gagal: popularTimesData });
        }
      }
      console.log("Re-Scrape berhasil!");

      return h.response({ status: "success", data: results }).code(201);
    },
  });

  // server.route({
  //   method: "POST",
  //   path: "/typical",
  //   handler: async (request, h) => {
  //     const { nama, hari, waktu } = request.payload;
  //     const { latitude, longitude } = await findCoordinate(nama);
  //     if (!latitude || !longitude || !hari || !waktu) {
  //       return h.response("Data screenshoot belum lengkap.").code(400);
  //     }
  //     try {
  //       const screenshotPath = await takeScreenshot(
  //         nama,
  //         latitude,
  //         longitude,
  //         hari,
  //         waktu
  //       );
  //       console.log("Berhasil menyimpan screenshoot");
  //       return h.file(screenshotPath).type("image/png");
  //     } catch (error) {
  //       console.error(error);
  //       return h.response("Error taking screenshot").code(500);
  //     }
  //   },
  // });

  // server.route({
  //   method: "POST",
  //   path: "/live",
  //   handler: async (request, h) => {
  //     const { nama } = request.payload;
  //     const { latitude, longitude } = await findCoordinate(nama);
  //     if (!latitude || !longitude) {
  //       return h.response("Data screenshoot belum lengkap.").code(400);
  //     }
  //     try {
  //       const screenshotPath = await takeScreenshot(nama, latitude, longitude);
  //       console.log("Berhasil menyimpan screenshoot");
  //       return h.file(screenshotPath).type("image/png");
  //     } catch (error) {
  //       console.error(error);
  //       return h.response("Error taking screenshot").code(500);
  //     }
  //   },
  // });

  server.route({
    method: "POST",
    path: "/screenshots/location",
    handler: async (request, h) => {
      const { nama, hari, waktu, type } = request.payload;
      const { latitude, longitude } = await findCoordinate(nama);
      if (!latitude || !longitude) {
        return h.response("Data screenshoot belum lengkap.").code(400);
      }
      if (type === "typical" && (!hari || !waktu)) {
        return h.response("Data screenshoot belum lengkap.").code(400);
      }
      try {
        const { timestamp, fileName, screenshotPath } =
          type === "typical"
            ? await takeScreenshot(nama, latitude, longitude, hari, waktu)
            : await takeScreenshot(nama, latitude, longitude);
        let newHari = hari;
        let newWaktu = waktu;
        if (type === "live") {
          const now = new Date();
          const dayIndex = now.getDay(); // Get the day index (0-6)
          const dayNames = [
            "Minggu",
            "Senin",
            "Selasa",
            "Rabu",
            "Kamis",
            "Jumat",
            "Sabtu",
          ];
          const dayName = dayNames[dayIndex];
          newHari = dayName;
          const findWaktu = screenshotPath.match(/(\d{2}-\d{2}-\d{2})/);
          newWaktu = findWaktu[0].replaceAll("-", ":");
        }

        // Python Image Processing
        const pythonPath =
          '"C:\\Users\\Marita Prasetyani\\AppData\\Local\\Programs\\Python\\Python313\\python.exe"';
        const pythonScript = path.join(__dirname, "cv.py");
        await new Promise((resolve, reject) => {
          exec(
            `${pythonPath} ${pythonScript} ${screenshotPath}`,
            (err, stdout, stderr) => {
              if (err) {
                console.error("Error processing image:", stderr);
                reject(new Error("Image processing failed."));
              } else {
                resolve();
              }
            }
          );
        });

        await saveScreenshot(
          timestamp,
          type,
          nama,
          newHari,
          newWaktu,
          screenshotPath
        );

        return h
          .response({
            jenis: type,
            nama: nama,
            hari: newHari,
            waktu: newWaktu,
            file: fileName,
            timestamp: timestamp,
          })
          .code(200);
      } catch (error) {
        const response = `Gagal melakukan screenshot pada lokasi: ${nama}`;
        console.log(response);
        console.error(error);
        return h.response(response).code(500);
      }
    },
  });

  server.route({
    method: "POST",
    path: "/screenshots/url",
    handler: async (request, h) => {
      const { url, hari, waktu, type } = request.payload;
      const { latitude, longitude, zoom, zoomType } = await filterGoogleMapsUrl(
        url
      );
      if (!latitude || !longitude || !zoom || !zoomType) {
        return h
          .response("Gagal melakukan pencarian pada google maps.")
          .code(400);
      }
      if (type === "typical" && (!hari || !waktu)) {
        return h.response("Data screenshoot belum lengkap.").code(400);
      }
      try {
        const { timestamp, fileName, screenshotPath } =
          type === "typical"
            ? await takeScreenshot(
                "manual",
                latitude,
                longitude,
                hari,
                waktu,
                zoom,
                zoomType
              )
            : await takeScreenshot(
                "manual",
                latitude,
                longitude,
                false,
                false,
                zoom,
                zoomType
              );
        let newUrl = url;
        let newHari = hari;
        let newWaktu = waktu;
        newUrl = `Koordinat ${url.split("@")[1].split("/")[0]}`;
        if (type === "live") {
          const now = new Date();
          const dayIndex = now.getDay(); // Get the day index (0-6)
          const dayNames = [
            "Minggu",
            "Senin",
            "Selasa",
            "Rabu",
            "Kamis",
            "Jumat",
            "Sabtu",
          ];
          const dayName = dayNames[dayIndex];
          newHari = dayName;
          const findWaktu = screenshotPath.match(/(\d{2}-\d{2}-\d{2})/);
          newWaktu = findWaktu[0].replaceAll("-", ":");
        }

        // Python Image Processing
        const pythonScript = path.join(__dirname, "cv.py");
        await new Promise((resolve, reject) => {
          exec(
            `python ${pythonScript} ${screenshotPath}`,
            (err, stdout, stderr) => {
              if (err) {
                console.error("Error processing image:", stderr);
                reject(new Error("Image processing failed."));
              } else {
                resolve();
              }
            }
          );
        });

        await saveScreenshot(
          timestamp,
          type,
          newUrl,
          newHari,
          newWaktu,
          screenshotPath
        );
        return h
          .response({
            jenis: type,
            nama: newUrl,
            hari: newHari,
            waktu: newWaktu,
            file: fileName,
            timestamp: timestamp,
          })
          .code(200);
      } catch (error) {
        const response = `Gagal melakukan screenshot pada link: ${url}`;
        console.log(response);
        console.error(error);
        return h.response(response).code(500);
      }
    },
  });

  server.route({
    method: "GET",
    path: "/screenshots/{filename}",
    handler: {
      directory: {
        path: path.join(__dirname, "screenshots"),
        listing: false,
      },
    },
  });

  server.ext("onPreResponse", (request, h) => {
    // mendapatkan konteks response dari request
    const { response } = request;

    if (response instanceof Error) {
      console.log(response);
      // penanganan client error secara internal.
      if (response instanceof ClientError) {
        const newResponse = h.response({
          status: "fail",
          message: response.message,
        });
        newResponse.code(response.statusCode);
        return newResponse;
      }

      // mempertahankan penanganan client error oleh hapi secara native, seperti 404, etc.
      if (!response.isServer) {
        return h.continue;
      }

      // penanganan server error sesuai kebutuhan
      const newResponse = h.response({
        status: "error",
        message: "terjadi kegagalan pada server kami",
      });
      newResponse.code(500);
      return newResponse;
    }

    // jika bukan error, lanjutkan dengan response sebelumnya (tanpa terintervensi)
    return h.continue;
  });

  await server.start();
  console.log("Server running on %s", server.info.uri);
};

init();

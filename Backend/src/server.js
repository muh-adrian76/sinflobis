require("dotenv").config();
const Hapi = require("@hapi/hapi");
const Inert = require("@hapi/inert");
const ClientError = require("./exceptions/ClientError");
const { scrapeGoogleMaps, saveToDatabase } = require("./script/scraper");
const mysql = require("mysql2/promise");

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

  const db = await mysql.createConnection({
    user: process.env.MYSQL_USER,
    host: process.env.MYSQL_HOST,
    database: process.env.MYSQL_DATABASE,
    password: process.env.MYSQL_PASSWORD,
    port: process.env.MYSQL_PORT,
  });

  await server.register(Inert);

  server.route({
    method: "GET",
    path: "/",
    handler: (request, h) => {
      return h.redirect(
        "http://localhost:80/dev/Project%20data%20mining/Sinflobis/"
      );
    },
  });

  server.route({
    method: "POST",
    path: "/scrape",
    handler: async (request, h) => {
      const { query, checkbox } = request.payload;
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
            await saveToDatabase(locationData, popularTimesData);
            results.push({
              berhasil: `${singleQuery} : <strong>tidak terdapat <i>missing values</i></strong>`,
              locationData,
              popularTimesData,
            });
          } else {
            missingValues = 168 - popularTimesData.length;
            if (checkbox) {
              await saveToDatabase(locationData, popularTimesData);
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
      return h.response({ status: "success", data: results }).code(201);
    },
  });

  server.route({
    method: "POST",
    path: "/grup-lokasi",
    handler: async (request, h) => {
      const { grup } = request.payload;
      if (grup) {
        const { nama, anggota } = grup;
        const membersString = anggota.join(",");
        const query =
          "INSERT INTO location_groups (name, member) VALUES (?, ?)";

        try {
          const [results] = await db.execute(query, [nama, membersString]);
          return h
            .response({
              status: "success",
              data: results,
              message: "Group saved successfully.",
            })
            .code(201);
        } catch (error) {
          console.error("Error inserting data:", error);
          return h
            .response({ status: "error", message: "Failed to save group." })
            .code(500);
        }
      } else {
        return h
          .response({ status: "error", message: "No group data received." })
          .code(400);
      }
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

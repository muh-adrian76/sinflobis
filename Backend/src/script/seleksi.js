require("dotenv").config();
const { createConnection } = require("mysql2/promise");

const saveCriteria = async (nama, sifat, kategori) => {
  const connection = await createConnection({
    user: process.env.MYSQL_USER,
    host: process.env.MYSQL_HOST,
    database: process.env.MYSQL_DATABASE,
    password: process.env.MYSQL_PASSWORD,
    port: process.env.MYSQL_PORT,
  });
  try {
    await connection.beginTransaction();

    let simpanType = "insert";
    const [criteriaChecks] = await connection.execute(
      "SELECT id FROM criterias WHERE nama=?",
      [nama]
    );
    if (criteriaChecks.length > 0) {
      criteriaId = criteriaChecks[0].id;
      await connection.execute(
        "UPDATE criterias SET nama=?, sifat=?, kategori=? WHERE id=?",
        [nama, sifat, kategori, criteriaId]
      );
      simpanType = "update";
      console.log(`Berhasil memperbarui kriteria ${nama} pada database.`);
    } else {
      const [result] = await connection.execute(
        "INSERT INTO criterias (nama, sifat, kategori) VALUES(?,?,?)",
        [nama, sifat, kategori]
      );
      criteriaId = result.insertId;
      console.log(`Berhasil menyimpan kriteria ${nama} pada database.`);
    }
    await connection.commit();
    return { criteriaId, simpanType };
  } catch (error) {
    console.error("Pesan error :", error);
    await connection.rollback();
  } finally {
    await connection.end();
  }
};

const calculateMerec = async (alternatives) => {
  const connection = await createConnection({
    user: process.env.MYSQL_USER,
    host: process.env.MYSQL_HOST,
    database: process.env.MYSQL_DATABASE,
    password: process.env.MYSQL_PASSWORD,
    port: process.env.MYSQL_PORT,
  });

  let matriks;
  let criteriaCount;
  try {
    await connection.beginTransaction();

    const [criteriaChecks] = await connection.execute(
      "SELECT kategori FROM criterias ORDER BY id ASC"
    );
    criteriaCount = criteriaChecks.length;
    if (criteriaChecks.length > 0) {
      const categories = criteriaChecks;

      // merge json
      matriks = alternatives.map((area) => {
        const mergedCriteria = {};
        const criteriaKeys = Object.keys(area.criteria);

        criteriaKeys.forEach((key, index) => {
          mergedCriteria[key] = {
            value: area.criteria[key],
            kategori: categories[index].kategori, // Match by index
          };
        });

        return {
          alternatif: area.name,
          criteria: mergedCriteria,
        };
      });
    }
    await connection.commit();
  } catch (error) {
    console.error("Pesan error :", error);
    await connection.rollback();
  } finally {
    await connection.end();
  }

  // Langkah 1: nilai min dan max
  const updatedMatriks = matriks.map((alternative) => {
    const updatedCriteria = {};
    Object.keys(alternative.criteria).forEach((criteriaKey) => {
      const { value, kategori } = alternative.criteria[criteriaKey];
      updatedCriteria[criteriaKey] = {
        value,
        kategori,
        selectedValue:
          kategori === "Beneficial"
            ? Math.min(
                ...matriks.map((item) =>
                  parseFloat(item.criteria[criteriaKey].value)
                )
              )
            : Math.max(
                ...matriks.map((item) =>
                  parseFloat(item.criteria[criteriaKey].value)
                )
              ),
      };
    });
    return {
      alternatif: alternative.alternatif,
      criteria: updatedCriteria,
    };
  });

  // Langkah 2: normalisasi
  const normalizedMatriks = updatedMatriks.map((alternative) => {
    const normalizedCriteria = {};
    Object.keys(alternative.criteria).forEach((criteriaKey) => {
      const { value, kategori, selectedValue } =
        alternative.criteria[criteriaKey];

      normalizedCriteria[criteriaKey] = {
        value: parseFloat(value),
        kategori,
        selectedValue: parseFloat(selectedValue),
        normalizedValue:
          kategori === "Beneficial"
            ? parseFloat(selectedValue) / parseFloat(value) // Beneficial: selectedValue / value
            : parseFloat(value) / parseFloat(selectedValue), // Non-Beneficial: value / selectedValue
      };
    });

    return {
      alternatif: alternative.alternatif,
      criteria: normalizedCriteria,
    };
  });

  // Langkah 3: Si
  const lnMatrix = normalizedMatriks.map((alternative) => {
    const lnCriteria = {};
    Object.keys(alternative.criteria).forEach((criteriaKey) => {
      const { normalizedValue } = alternative.criteria[criteriaKey];
      const lnValue = Math.log(normalizedValue); // Calculate ln(normalizedValue)
      lnCriteria[criteriaKey] = {
        ln: Math.abs(lnValue), // Get the absolute value
      };
    });

    return {
      alternatif: alternative.alternatif,
      criteria: lnCriteria,
    };
  });
  const siMatrix = lnMatrix.map((alternative) => {
    const sumAbsLnValues = Object.values(alternative.criteria).reduce(
      (sum, { ln }) => sum + ln,
      0
    );
    const result = Math.log(1 + (1 / criteriaCount) * sumAbsLnValues);

    return {
      alternatif: alternative.alternatif,
      Si: result,
    };
  });

  // Langkah 4: Sij
  const sijMatrix = lnMatrix.map((alternative) => {
    const sijCriteria = {};
    Object.keys(alternative.criteria).forEach(
      (criteriaKey, _, criteriaKeys) => {
        const sumAbsExcludingCurrent = criteriaKeys
          .filter((key) => key !== criteriaKey) // Exclude current criterion
          .reduce((sum, key) => {
            const absValue = alternative.criteria[key]?.ln;
            if (typeof absValue === "number") {
              return sum + absValue;
            } else {
              console.warn(
                `Missing absoluteLnValue for ${key} in ${alternative.name}`
              );
              return sum;
            }
          }, 0);

        if (sumAbsExcludingCurrent > 0) {
          const sij = Math.log(
            1 + (1 / criteriaCount) * sumAbsExcludingCurrent
          );
          sijCriteria[criteriaKey] = sij; // Store the result for the current criterion
        } else {
          sijCriteria[criteriaKey] = null; // Fallback for invalid calculations
        }
      }
    );
    return {
      alternatif: alternative.alternatif,
      Sij: sijCriteria,
    };
  });

  // Langkah 5: Ej
  const ejMatrix = {};
  Object.keys(sijMatrix[0].Sij).forEach((criteriaKey) => {
    // Sum absolute differences for all alternatives for the current criterion
    const ejValue = sijMatrix.reduce((sum, alternative, index) => {
      const sijValue = alternative.Sij[criteriaKey]; // Value from sijMatrix
      const siValue = siMatrix[index].Si; // Corresponding value from siMatrix

      // Add the absolute difference to the sum
      return sum + Math.abs(sijValue - siValue);
    }, 0);

    // Store the calculated E_j value
    ejMatrix[criteriaKey] = ejValue;
  });

  // Langkah 6: Wj
  const pembagi = Object.values(ejMatrix).reduce(
    (sum, ejValue) => sum + ejValue,
    0
  );
  const wjMatrix = {};
  Object.keys(ejMatrix).forEach((criteriaKey) => {
    wjMatrix[criteriaKey] = ejMatrix[criteriaKey] / pembagi;
  });

  return wjMatrix;
};

const calculateTopsis = async (matriks, bobot) => {
  const connection = await createConnection({
    user: process.env.MYSQL_USER,
    host: process.env.MYSQL_HOST,
    database: process.env.MYSQL_DATABASE,
    password: process.env.MYSQL_PASSWORD,
    port: process.env.MYSQL_PORT,
  });

  let topsisMatriks;
  try {
    await connection.beginTransaction();

    const [criteriaChecks] = await connection.execute(
      "SELECT sifat FROM criterias ORDER BY id ASC"
    );
    if (criteriaChecks.length > 0) {
      const attributes = criteriaChecks;

      // merge json
      topsisMatriks = matriks.map((area) => {
        const mergedCriteria = {};
        const criteriaKeys = Object.keys(area.criteria);

        criteriaKeys.forEach((key, index) => {
          mergedCriteria[key] = {
            value: area.criteria[key],
            sifat: attributes[index].sifat, // Match by index
          };
        });

        return {
          alternatif: area.name,
          criteria: mergedCriteria,
        };
      });
    }
    await connection.commit();
  } catch (error) {
    console.error("Pesan error :", error);
    await connection.rollback();
  } finally {
    await connection.end();
  }

  // Langkah 1: matriks ternormalisasi
  const squaredSums = {};
  topsisMatriks.forEach((alternative) => {
    Object.entries(alternative.criteria).forEach(
      ([criteriaKey, criteriaData]) => {
        const value = parseFloat(criteriaData.value);
        if (!squaredSums[criteriaKey]) {
          squaredSums[criteriaKey] = 0;
        }
        squaredSums[criteriaKey] += Math.pow(value, 2); // Add the square of the value
      }
    );
  });
  const pembagi = {};
  Object.entries(squaredSums).forEach(([criteriaKey, sum]) => {
    pembagi[criteriaKey] = Math.sqrt(sum);
  });
  const topsisUpdatedMatrix = topsisMatriks.map((alternative) => {
    const normalizedCriteria = {};
    Object.entries(alternative.criteria).forEach(
      ([criteriaKey, criteriaData]) => {
        const value = parseFloat(criteriaData.value);
        normalizedCriteria[criteriaKey] = {
          ...criteriaData,
          normalized: value / pembagi[criteriaKey], // Normalisasi dengan pembagi
        };
      }
    );

    return {
      alternatif: alternative.alternatif,
      criteria: normalizedCriteria,
    };
  });

  // Langkah 2: normalisasi terbobot
  const topsisNormalizedWeight = topsisUpdatedMatrix.map((alternative) => {
    const weightedCriteria = {};
    Object.entries(alternative.criteria).forEach(
      ([criteriaKey, criteriaData]) => {
        const bobotKriteria = parseFloat(bobot[criteriaKey] || 0); // Bobot dari parameter
        weightedCriteria[criteriaKey] = {
          ...criteriaData,
          weighted: criteriaData.normalized * bobotKriteria, // Mengalikan normalisasi dengan bobot
        };
      }
    );

    return {
      alternatif: alternative.alternatif,
      criteria: weightedCriteria,
    };
  });

  // Langkah 3: solusi ideal positif dan negatif
  const criteriaKeys = Object.keys(topsisNormalizedWeight[0].criteria);
  const solusiIdealPositif = {}; // A⁺
  const solusiIdealNegatif = {}; // A⁻

  criteriaKeys.forEach((criteriaKey) => {
    const weightedValues = topsisNormalizedWeight.map(
      (alt) => alt.criteria[criteriaKey].weighted
    );
    const sifat = topsisNormalizedWeight[0].criteria[criteriaKey].sifat;

    // Calculate A⁺ (Ideal Positive Solution)
    if (sifat === "Benefit") {
      solusiIdealPositif[criteriaKey] = Math.max(...weightedValues);
    } else if (sifat === "Cost") {
      solusiIdealPositif[criteriaKey] = Math.min(...weightedValues);
    }

    // Calculate A⁻ (Ideal Negative Solution)
    if (sifat === "Benefit") {
      solusiIdealNegatif[criteriaKey] = Math.min(...weightedValues);
    } else if (sifat === "Cost") {
      solusiIdealNegatif[criteriaKey] = Math.max(...weightedValues);
    }
  });

  // Langkah 4: jarak ideal positif dan negatif (euclidean)
  const calculateEuclideanDistances = (
    normalizedWeightMatrix,
    solusiIdealPositif,
    solusiIdealNegatif
  ) => {
    return normalizedWeightMatrix.map((alternative) => {
      let distancePositive = 0; // D⁺
      let distanceNegative = 0; // D⁻

      Object.entries(alternative.criteria).forEach(
        ([criteriaKey, criteriaData]) => {
          const weightedValue = criteriaData.weighted;

          // Jarak ke solusi ideal positif (A⁺)
          const diffPositive = weightedValue - solusiIdealPositif[criteriaKey];
          distancePositive += Math.pow(diffPositive, 2);

          // Jarak ke solusi ideal negatif (A⁻)
          const diffNegative = weightedValue - solusiIdealNegatif[criteriaKey];
          distanceNegative += Math.pow(diffNegative, 2);
        }
      );

      return {
        alternatif: alternative.alternatif,
        dPositive: Math.sqrt(distancePositive), // Square root of summed positive distance
        dNegative: Math.sqrt(distanceNegative),
      };
    });
  };
  const euclidean = calculateEuclideanDistances(
    topsisNormalizedWeight,
    solusiIdealPositif,
    solusiIdealNegatif
  );

  // Langkah 5: nilai preferensi
  const calculatePreferenceValues = (distances) => {
    return distances.map(({ alternatif, dPositive, dNegative }) => {
      const sumDistances = dPositive + dNegative;

      // Handle division by zero
      const preferenceValue = sumDistances === 0 ? 0 : dNegative / sumDistances;

      return {
        alternatif,
        dPositive,
        dNegative,
        preferenceValue,
      };
    });
  };
  const preference = calculatePreferenceValues(euclidean);

  return {
    topsisMatriks,
    topsisUpdatedMatrix,
    topsisNormalizedWeight,
    solusiIdealPositif,
    solusiIdealNegatif,
    euclidean,
    preference,
  };
};

const calculateWaspas = async (matriks, bobot) => {
  const connection = await createConnection({
    user: process.env.MYSQL_USER,
    host: process.env.MYSQL_HOST,
    database: process.env.MYSQL_DATABASE,
    password: process.env.MYSQL_PASSWORD,
    port: process.env.MYSQL_PORT,
  });

  let waspasMatriks;
  try {
    await connection.beginTransaction();

    const [criteriaChecks] = await connection.execute(
      "SELECT sifat FROM criterias ORDER BY id ASC"
    );
    if (criteriaChecks.length > 0) {
      const attributes = criteriaChecks;

      // merge json
      waspasMatriks = matriks.map((area) => {
        const mergedCriteria = {};
        const criteriaKeys = Object.keys(area.criteria);

        criteriaKeys.forEach((key, index) => {
          mergedCriteria[key] = {
            value: area.criteria[key],
            sifat: attributes[index].sifat, // Match by index
          };
        });

        return {
          alternatif: area.name,
          criteria: mergedCriteria,
        };
      });
    }
    await connection.commit();
  } catch (error) {
    console.error("Pesan error :", error);
    await connection.rollback();
  } finally {
    await connection.end();
  }

  // Langkah 1: matriks ternormalisasi
  const normalizedMatrix = waspasMatriks.map((alternative) => {
    const normalizedCriteria = {};
    Object.entries(alternative.criteria).forEach(
      ([criteriaKey, criteriaData]) => {
        const allValues = waspasMatriks.map(
          (alt) => alt.criteria[criteriaKey].value
        ); // Collect all values for the criterion

        if (criteriaData.sifat === "Benefit") {
          // Normalize for Benefit
          normalizedCriteria[criteriaKey] = {
            ...criteriaData,
            normalized: criteriaData.value / Math.max(...allValues),
          };
        } else if (criteriaData.sifat === "Cost") {
          // Normalize for Cost
          normalizedCriteria[criteriaKey] = {
            ...criteriaData,
            normalized: Math.min(...allValues) / criteriaData.value,
          };
        }
      }
    );

    return {
      alternatif: alternative.alternatif,
      criteria: normalizedCriteria,
    };
  });

  // Langkah 2: matriks WSM (Weighted Sum Model)
  const wsmMatrix = normalizedMatrix.map((alternative) => {
    let weightedSum = 0;

    const weightedCriteria = {};
    Object.entries(alternative.criteria).forEach(
      ([criteriaKey, criteriaData]) => {
        const weight = parseFloat(bobot[criteriaKey] || 0);
        const weightedValue = criteriaData.normalized * weight;

        weightedCriteria[criteriaKey] = {
          ...criteriaData,
          weighted: weightedValue,
        };

        weightedSum += weightedValue;
      }
    );

    return {
      alternatif: alternative.alternatif,
      criteria: weightedCriteria,
      wsm: weightedSum,
    };
  });

  // Langkah 3: matriks WPM (Weighted Product Model)
  const wpmMatrix = normalizedMatrix.map((alternative) => {
    let weightedProduct = 1;

    const weightedCriteria = {};
    Object.entries(alternative.criteria).forEach(
      ([criteriaKey, criteriaData]) => {
        const weight = parseFloat(bobot[criteriaKey] || 0); // Get weight for the criterion
        const poweredValue = Math.pow(criteriaData.normalized, weight); // Raise normalized value to the power of its weight

        weightedCriteria[criteriaKey] = {
          ...criteriaData,
          powered: poweredValue,
        };

        weightedProduct *= poweredValue; // Multiply to calculate the product
      }
    );

    return {
      alternatif: alternative.alternatif,
      criteria: weightedCriteria,
      wpm: weightedProduct, // Final product result
    };
  });

  // Langkah 4: matriks WASPAS
  const lambda = 0.5;
  const preferenceMatrix = wsmMatrix.map((wsmAlternative, index) => {
    const wpmAlternative = wpmMatrix[index];
    if (wsmAlternative.alternatif !== wpmAlternative.alternatif) {
      throw new Error("Mismatched alternatives between WSM and WPM matrices");
    }

    const preferenceValue =
      lambda * wsmAlternative.wsm + (1 - lambda) * wpmAlternative.wpm;

    return {
      alternatif: wsmAlternative.alternatif,
      wsm: wsmAlternative.wsm,
      wpm: wpmAlternative.wpm,
      preferenceValue,
    };
  });

  return {
    waspasMatriks,
    normalizedMatrix,
    wsmMatrix,
    wpmMatrix,
    preferenceMatrix,
  };
};

const generateTopsisPage = async (
  matriks,
  bobot,
  ternormalisasi,
  terbobot,
  solusiIdealPositif,
  solusiIdealNegatif,
  jarakEuclidean,
  preferensi
) => {
  const rankedPreferences = preferensi
    .slice() // Salin data untuk menghindari modifikasi langsung
    .sort((a, b) => b.preferenceValue - a.preferenceValue) // Urutkan secara descending
    .map((item, index) => ({
      rank: index + 1, // Tambahkan ranking
      ...item,
    }));

  const criteriaDetails = Object.keys(matriks[0].criteria).map((key) => ({
    key,
    sifat: matriks[0].criteria[key].sifat,
    bobot: parseFloat(bobot[key]),
  }));

  const htmlTopsis = `
<!DOCTYPE html>
<html>
<head>
  <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet" />

  <title>Laporan Perhitungan TOPSIS</title>
  <style>
    body { font-family: 'Public Sans', sans-serif; margin: 20px; }
    table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
    th, td { border: 1px solid #ddd; text-align: left; padding: 8px; }
    th { background-color: #f4f4f4; }
    h1, h2 { color: #333; }
    .section { margin-bottom: 40px; }
    p { color: #555; }
  </style>
</head>
<body>
  <h1>Laporan Perhitungan TOPSIS</h1>

  <!-- Tabel Bobot dan Sifat -->
  <div class="section">
    <h2>Bobot dan Sifat Kriteria</h2>
    <table>
      <thead>
        <tr>
          <th>Kriteria</th>
          <th>Bobot</th>
          <th>Sifat</th>
        </tr>
      </thead>
      <tbody>
        ${criteriaDetails
          .map(
            (detail) => `
          <tr>
            <td>${detail.key}</td>
            <td>${
              typeof detail.bobot === "number" ? detail.bobot.toFixed(4) : "N/A"
            }</td>
            <td>${detail.sifat}</td>
          </tr>`
          )
          .join("")}
      </tbody>
    </table>
  </div>

  <!-- Langkah 1: Matriks Keputusan Awal -->
  <div class="section">
    <h2>Langkah 1: Matriks Keputusan Awal</h2>
    <p>Matriks keputusan awal menunjukkan nilai kriteria untuk setiap alternatif yang akan dievaluasi.</p>
    <table>
      <thead>
        <tr>
          <th>Alternatif</th>
          ${Object.keys(matriks[0].criteria)
            .map((c) => `<th>${c}</th>`)
            .join("")}
        </tr>
      </thead>
      <tbody>
        ${matriks
          .map(
            (row) => `
          <tr>
            <td>${row.alternatif}</td>
            ${Object.values(row.criteria)
              .map((c) => `<td>${c.value}</td>`)
              .join("")}
          </tr>`
          )
          .join("")}
      </tbody>
    </table>
  </div>

  <!-- Langkah 2: Matriks Ternormalisasi -->
  <div class="section">
    <h2>Langkah 2: Matriks Ternormalisasi</h2>
    <p>Matriks ternormalisasi menghitung nilai relatif setiap kriteria berdasarkan nilai awalnya.</p>
    <table>
      <thead>
        <tr>
          <th>Alternatif</th>
          ${Object.keys(ternormalisasi[0].criteria)
            .map((c) => `<th>${c}</th>`)
            .join("")}
        </tr>
      </thead>
      <tbody>
        ${ternormalisasi
          .map(
            (row) => `
          <tr>
            <td>${row.alternatif}</td>
            ${Object.values(row.criteria)
              .map((c) => `<td>${c.normalized.toFixed(4)}</td>`)
              .join("")}
          </tr>`
          )
          .join("")}
      </tbody>
    </table>
  </div>

  <!-- Langkah 3: Matriks Ternormalisasi Terbobot -->
  <div class="section">
    <h2>Langkah 3: Matriks Ternormalisasi Terbobot</h2>
    <p>Matriks ini menghitung bobot untuk setiap nilai ternormalisasi berdasarkan bobot kriteria.</p>
    <table>
      <thead>
        <tr>
          <th>Alternatif</th>
          ${Object.keys(terbobot[0].criteria)
            .map((c) => `<th>${c}</th>`)
            .join("")}
        </tr>
      </thead>
      <tbody>
        ${terbobot
          .map(
            (row) => `
          <tr>
            <td>${row.alternatif}</td>
            ${Object.values(row.criteria)
              .map((c) => `<td>${c.weighted.toFixed(4)}</td>`)
              .join("")}
          </tr>`
          )
          .join("")}
      </tbody>
    </table>
  </div>

  <!-- Langkah 4: Solusi Ideal Positif dan Negatif -->
  <div class="section">
    <h2>Langkah 4: Solusi Ideal Positif dan Negatif</h2>
    <p>Solusi ideal positif (A⁺) dan solusi ideal negatif (A⁻) adalah nilai terbaik dan terburuk untuk setiap kriteria.</p>
    <table>
      <thead>
        <tr>
          <th>Kriteria</th>
          <th>Ideal Positif (A⁺)</th>
          <th>Ideal Negatif (A⁻)</th>
        </tr>
      </thead>
      <tbody>
        ${Object.keys(solusiIdealPositif)
          .map(
            (key) => `
          <tr>
            <td>${key}</td>
            <td>${solusiIdealPositif[key].toFixed(4)}</td>
            <td>${solusiIdealNegatif[key].toFixed(4)}</td>
          </tr>`
          )
          .join("")}
      </tbody>
    </table>
  </div>

  <!-- Langkah 5: Jarak Euclidean -->
  <div class="section">
    <h2>Langkah 5: Jarak Euclidean</h2>
    <p>Jarak Euclidean dihitung untuk menentukan seberapa dekat alternatif terhadap solusi ideal positif dan negatif.</p>
    <table>
      <thead>
        <tr>
          <th>Alternatif</th>
          <th>Jarak ke A⁺ (D⁺)</th>
          <th>Jarak ke A⁻ (D⁻)</th>
        </tr>
      </thead>
      <tbody>
        ${jarakEuclidean
          .map(
            (row) => `
          <tr>
            <td>${row.alternatif}</td>
            <td>${row.dPositive.toFixed(4)}</td>
            <td>${row.dNegative.toFixed(4)}</td>
          </tr>`
          )
          .join("")}
      </tbody>
    </table>
  </div>

  <!-- Langkah 6: Nilai Preferensi -->
  <div class="section">
    <h2>Langkah 6: Nilai Preferensi</h2>
    <p>Nilai preferensi dihitung untuk menentukan peringkat akhir alternatif berdasarkan kedekatannya ke solusi ideal.</p>
    <table>
      <thead>
        <tr>
          <th>Alternatif</th>
          <th>Nilai Preferensi</th>
        </tr>
      </thead>
      <tbody>
        ${preferensi
          .map(
            (row) => `
          <tr>
            <td>${row.alternatif}</td>
            <td>${row.preferenceValue.toFixed(4)}</td>
          </tr>`
          )
          .join("")}
      </tbody>
    </table>
  </div>

   <!-- Langkah 7: Perankingan -->
  <div class="section">
    <h2>Langkah 7: Perankingan</h2>
    <p>Alternatif diurutkan berdasarkan nilai preferensi (nilai tertinggi peringkat 1).</p>
    <table>
      <thead>
        <tr>
          <th>Peringkat</th>
          <th>Alternatif</th>
          <th>Nilai Preferensi</th>
        </tr>
      </thead>
      <tbody>
        ${rankedPreferences
          .map(
            (row) => `
          <tr>
            <td>${row.rank}</td>
            <td>${row.alternatif}</td>
            <td>${row.preferenceValue.toFixed(4)}</td>
          </tr>`
          )
          .join("")}
      </tbody>
    </table>
  </div>
</body>
</html>
`;
  return htmlTopsis;
};

const generateWaspasPage = async (
  matriks,
  bobot,
  ternormalisasi,
  wsm,
  wpm,
  preferensi
) => {
  const rankedPreferences = preferensi
    .slice() // Salin data untuk menghindari modifikasi langsung
    .sort((a, b) => b.preferenceValue - a.preferenceValue) // Urutkan secara descending
    .map((item, index) => ({
      rank: index + 1, // Tambahkan ranking
      ...item,
    }));

  const criteriaDetails = Object.keys(matriks[0].criteria).map((key) => ({
    key,
    sifat: matriks[0].criteria[key].sifat,
    bobot: parseFloat(bobot[key]),
  }));

  const htmlWaspas = `
<!DOCTYPE html>
<html>
<head>
  <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet" />
  <title>Laporan Perhitungan WASPAS</title>
  <style>
    body { font-family: 'Public Sans', sans-serif; margin: 20px; }
    table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
    th, td { border: 1px solid #ddd; text-align: left; padding: 8px; }
    th { background-color: #f4f4f4; }
    h1, h2 { color: #333; }
    .section { margin-bottom: 40px; }
    p { color: #555; }
  </style>
</head>
<body>
  <h1>Laporan Perhitungan WASPAS</h1>

  <!-- Tabel Bobot dan Sifat -->
  <div class="section">
    <h2>Bobot dan Sifat Kriteria</h2>
    <table>
      <thead>
        <tr>
          <th>Kriteria</th>
          <th>Bobot</th>
          <th>Sifat</th>
        </tr>
      </thead>
      <tbody>
        ${criteriaDetails
          .map(
            (detail) => `
          <tr>
            <td>${detail.key}</td>
            <td>${detail.bobot.toFixed(4)}</td>
            <td>${detail.sifat}</td>
          </tr>`
          )
          .join("")}
      </tbody>
    </table>
  </div>


  <!-- Langkah 1: Matriks Keputusan Awal -->
  <div class="section">
    <h2>Langkah 1: Matriks Keputusan Awal</h2>
    <p>Matriks keputusan awal menunjukkan nilai kriteria untuk setiap alternatif yang akan dievaluasi.</p>
    <table>
      <thead>
        <tr>
          <th>Alternatif</th>
          ${Object.keys(matriks[0].criteria)
            .map((c) => `<th>${c}</th>`)
            .join("")}
        </tr>
      </thead>
      <tbody>
        ${matriks
          .map(
            (row) => `
          <tr>
            <td>${row.alternatif}</td>
            ${Object.values(row.criteria)
              .map((c) => `<td>${c.value}</td>`)
              .join("")}
          </tr>`
          )
          .join("")}
      </tbody>
    </table>
  </div>

  <!-- Langkah 2: Matriks Ternormalisasi -->
  <div class="section">
    <h2>Langkah 2: Matriks Ternormalisasi</h2>
    <p>Matriks ternormalisasi menghitung nilai relatif setiap kriteria berdasarkan nilai awalnya.</p>
    <table>
      <thead>
        <tr>
          <th>Alternatif</th>
          ${Object.keys(ternormalisasi[0].criteria)
            .map((c) => `<th>${c}</th>`)
            .join("")}
        </tr>
      </thead>
      <tbody>
        ${ternormalisasi
          .map(
            (row) => `
          <tr>
            <td>${row.alternatif}</td>
            ${Object.values(row.criteria)
              .map((c) => `<td>${c.normalized.toFixed(4)}</td>`)
              .join("")}
          </tr>`
          )
          .join("")}
      </tbody>
    </table>
  </div>

  <!-- Langkah 3: Perhitungan WSM -->
  <div class="section">
    <h2>Langkah 3: Perhitungan WSM</h2>
    <p>WSM (Weighted Sum Model) dihitung dengan menjumlahkan nilai kriteria yang telah dikalikan dengan bobotnya.</p>
    <table>
      <thead>
        <tr>
          <th>Alternatif</th>
          <th>Nilai WSM</th>
        </tr>
      </thead>
      <tbody>
        ${wsm
          .map(
            (row) => `
          <tr>
            <td>${row.alternatif}</td>
            <td>${row.wsm.toFixed(4)}</td>
          </tr>`
          )
          .join("")}
      </tbody>
    </table>
  </div>

  <!-- Langkah 4: Perhitungan WPM -->
  <div class="section">
    <h2>Langkah 4: Perhitungan WPM</h2>
    <p>WPM (Weighted Product Model) dihitung dengan mengalikan nilai kriteria yang telah dipangkatkan dengan bobotnya.</p>
    <table>
      <thead>
        <tr>
          <th>Alternatif</th>
          <th>Nilai WPM</th>
        </tr>
      </thead>
      <tbody>
        ${wpm
          .map(
            (row) => `
          <tr>
            <td>${row.alternatif}</td>
            <td>${row.wpm.toFixed(4)}</td>
          </tr>`
          )
          .join("")}
      </tbody>
    </table>
  </div>

  <!-- Langkah 5: Nilai Preferensi -->
  <div class="section">
    <h2>Langkah 5: Nilai Preferensi</h2>
    <p>Nilai preferensi dihitung menggunakan kombinasi WSM dan WPM dengan rumus WASPAS.</p>
    <table>
      <thead>
        <tr>
          <th>Alternatif</th>
          <th>Nilai Preferensi</th>
        </tr>
      </thead>
      <tbody>
        ${preferensi
          .map(
            (row) => `
          <tr>
            <td>${row.alternatif}</td>
            <td>${row.preferenceValue.toFixed(4)}</td>
          </tr>`
          )
          .join("")}
      </tbody>
    </table>
  </div>

  <!-- Langkah 6: Perankingan -->
  <div class="section">
    <h2>Langkah 6: Perankingan</h2>
    <p>Alternatif diurutkan berdasarkan nilai preferensi (nilai tertinggi peringkat 1).</p>
    <table>
      <thead>
        <tr>
          <th>Peringkat</th>
          <th>Alternatif</th>
          <th>Nilai Preferensi</th>
        </tr>
      </thead>
      <tbody>
        ${rankedPreferences
          .map(
            (row) => `
          <tr>
            <td>${row.rank}</td>
            <td>${row.alternatif}</td>
            <td>${row.preferenceValue.toFixed(4)}</td>
          </tr>`
          )
          .join("")}
      </tbody>
    </table>
  </div>
</body>
</html>
`;
  return htmlWaspas;
};

module.exports = {
  saveCriteria,
  calculateMerec,
  calculateTopsis,
  calculateWaspas,
  generateTopsisPage,
  generateWaspasPage,
};

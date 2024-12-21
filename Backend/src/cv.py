import sys
import cv2
import numpy as np
import matplotlib.pyplot as plt
import json

# Mendapatkan path gambar dari argumen
image_path = sys.argv[1]

# Fungsi untuk mengonversi warna RGB ke format HSV
def rgb_to_hsv(color):
    rgb = np.uint8([[color]])  # Mengonversi input RGB menjadi array NumPy 1x1x3
    hsv = cv2.cvtColor(rgb, cv2.COLOR_RGB2HSV)[0][0]  # Mengonversi RGB ke HSV
    return hsv

# Membaca gambar menggunakan OpenCV
img = cv2.imread(image_path)
hsv_map = cv2.cvtColor(img, cv2.COLOR_BGR2HSV)  # Mengonversi gambar BGR ke HSV

# Inisialisasi data lalu lintas untuk gambar ini
traffic_data = []

# Mendefinisikan warna-warna untuk klasifikasi kemacetan dalam format RGB, kemudian dikonversi ke HSV
green_hsv = rgb_to_hsv([17, 214, 143])  # Warna hijau (lalu lintas normal)
yellow_hsv = rgb_to_hsv([255, 207, 67])  # Warna kuning (sedikit macet)
orange_hsv = rgb_to_hsv([247, 78, 59])  # Warna oranye (macet)
red_hsv = rgb_to_hsv([169, 39, 39])  # Warna merah (kemacetan tinggi)

# Menentukan rentang warna HSV dengan toleransi kecil untuk setiap level kemacetan
color_ranges = {
    "Green": (green_hsv - np.array([10, 50, 50]), green_hsv + np.array([10, 50, 50])),
    "Yellow": (yellow_hsv - np.array([10, 50, 50]), yellow_hsv + np.array([10, 50, 50])),
    "Orange": (orange_hsv - np.array([10, 100, 100]), orange_hsv + np.array([10, 100, 100])),
    "Red": (red_hsv - np.array([10, 50, 50]), red_hsv + np.array([10, 50, 50])),
}

# Inisialisasi mask untuk setiap level kemacetan dan penyimpanan data lalu lintas
traffic_masks = {}

# Proses deteksi untuk setiap level warna, kecuali hijau (lalu lintas normal)
for level, (lower, upper) in color_ranges.items():
    if level == "Green":  # Melewatkan warna hijau (lalu lintas normal)
        continue
    mask = cv2.inRange(hsv_map, lower, upper)  # Membuat mask untuk rentang warna tertentu
    traffic_masks[level] = mask
    contours, _ = cv2.findContours(mask, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)  # Mencari kontur pada mask
    for contour in contours:
        area = cv2.contourArea(contour)  # Menghitung luas area kontur
        if area > 95:  # Menyaring area kecil agar tidak dianggap noise
            traffic_data.append((area, contour))  # Menambahkan data area dan kontur ke list traffic_data

# Mengurutkan data lalu lintas berdasarkan luas area (terbesar ke terkecil)
traffic_data.sort(key=lambda x: x[0], reverse=True)

# Menggambar hasil deteksi pada gambar asli
bounding_box_points = []
for rank, (area, contour) in enumerate(traffic_data, start=1):
    x, y, w, h = cv2.boundingRect(contour)  # Mendapatkan bounding box untuk setiap kontur
    # Menyaring fitur non-jalan berdasarkan rasio aspek (misalnya logo atau simbol yang terlalu kecil)
    aspect_ratio = w / h if h != 0 else 0
    if aspect_ratio < 0.2 or aspect_ratio > 5:  # Mengabaikan bentuk yang sangat sempit atau persegi
        continue
    cv2.rectangle(img, (x, y), (x + w, y + h), (255, 0, 0), 2)  # Menggambar kotak di sekitar kontur
    cv2.putText(img, f"{rank}", (x, y - 10), cv2.FONT_HERSHEY_DUPLEX, 0.75, (0, 0, 0), 2)  # Menambahkan nomor peringkat
    # Simpan koordinat sudut bounding box
    position = (x, y, x + w, y + h)
    bounding_box_points.append({
        "rank": rank,
        "position": position
    })

# Mengonversi gambar ke format RGB untuk ditampilkan dengan matplotlib
result_img = cv2.cvtColor(img, cv2.COLOR_BGR2RGB)
print(json.dumps(bounding_box_points))

# Membuat plot dan menyimpan hasilnya sebagai file gambar
plt.figure(figsize=(16, 9))
plt.imshow(result_img)
plt.axis('off')
# plt.title('Traffic Levels Detected')
plt.savefig(image_path, bbox_inches="tight", dpi=400)  # Simpan plot ke file
plt.close()

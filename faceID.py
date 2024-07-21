import mysql.connector
import cv2
import numpy as np
from PIL import Image
import io

# SQLにアクセス
conn = mysql.connector.connect(
    host="localhost",
    user="root",
    database="faceID"
)

conn.ping(reconnect=True)
cursor = conn.cursor(dictionary=True)

# 認証対象のデータを取得
sql = "SELECT * FROM ninsho"
cursor.execute(sql)
result = cursor.fetchall()

first_row = result[0]
name = first_row['name']
face_binary = first_row['face_picture']

# 認証対象の顔画像を取得
sql_2 = "SELECT face_picture FROM face WHERE name = %s"
cursor.execute(sql_2, (name,))
login_result = cursor.fetchall()

if len(login_result) == 0:
    print("存在しない名前です")
    # 認証する顔写真を消す
    sql_delete = "DELETE FROM ninsho"
    cursor.execute(sql_delete)
    conn.commit()
    # データベース接続を閉じる
    cursor.close()
    conn.close()
    exit()  # エラーが発生した場合、スクリプトを終了します


def binary_to_image(binary_data):
    image = Image.open(io.BytesIO(binary_data))
    return cv2.cvtColor(np.array(image), cv2.COLOR_RGB2BGR)


face_image = binary_to_image(face_binary)
login_image = binary_to_image(login_result[0]['face_picture'])


def detect_and_align_face(image):
    gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
    face_cascade = cv2.CascadeClassifier(
        cv2.data.haarcascades + 'haarcascade_frontalface_default.xml')
    faces = face_cascade.detectMultiScale(gray, 1.1, 4)

    if len(faces) == 0:
        return None

    (x, y, w, h) = faces[0]
    face = image[y:y+h, x:x+w]

    # 顔を正規化（サイズ統一）
    face_aligned = cv2.resize(face, (200, 200))

    return face_aligned


def extract_features(face):
    if face is None:
        return None

    # グレースケールに変換
    gray = cv2.cvtColor(face, cv2.COLOR_BGR2GRAY)

    # HOG特徴量を計算
    hog = cv2.HOGDescriptor()
    features = hog.compute(gray)

    return features.flatten()


def compare_faces(face1, face2):
    if face1 is None or face2 is None:
        return False

    # コサイン類似度を計算
    similarity = np.dot(face1, face2) / \
        (np.linalg.norm(face1) * np.linalg.norm(face2))

    # 閾値を設定（要調整）
    threshold = 0.7
    return similarity > threshold


# 顔検出と特徴抽出
face1 = detect_and_align_face(face_image)
face2 = detect_and_align_face(login_image)

if face1 is None or face2 is None:
    print("顔が検出されませんでした")
else:
    features1 = extract_features(face1)
    features2 = extract_features(face2)

    if compare_faces(features1, features2):
        print("認証成功")
    else:
        print("認証失敗")

# 認証する顔写真を消す
sql_delete = "DELETE FROM ninsho"
cursor.execute(sql_delete)
conn.commit()

# データベース接続を閉じる
cursor.close()
conn.close()

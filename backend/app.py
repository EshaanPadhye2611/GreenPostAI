import numpy as np
from flask import Flask, request, jsonify, render_template
from tensorflow.keras.models import load_model
from tensorflow.keras.preprocessing import image
import torch
import cv2
import os
import mysql.connector
from datetime import datetime

# Initialize Flask app
app = Flask(__name__)

# MySQL database connection
def get_db_connection():
    conn = mysql.connector.connect(
        host="localhost",
        user="root",  # replace with your MySQL username
        password="astro",  # replace with your MySQL password
        database="post_office"  # your database name
    )
    return conn

# Load YOLOv5 model for object detection
yolo_model = torch.hub.load('ultralytics/yolov5', 'yolov5s', pretrained=True)

# Load clean vs dirty classification model
clean_dirty_model = load_model('clean_dirty_model.h5')

# Function to process a single image
def test_single_image(img_path, image_id):
    try:
        img_path = img_path.strip()  
        img = image.load_img(img_path, target_size=(150, 150))
        img_array = image.img_to_array(img) / 255.0  # Rescale image
        img_array = np.expand_dims(img_array, axis=0)  # Add batch dimension

        # Step 2: Predict clean or dirty using the classification model
        prediction = clean_dirty_model.predict(img_array)
        is_dirty = prediction[0] > 0.5

        if is_dirty:
            status = "Dirty"
        else:
            status = "Clean"

        # Save the clean or dirty image without modification
        img_cv2 = cv2.imread(img_path)
        output_img_path = os.path.join("static", f"output_image_{image_id}.jpg")
        cv2.imwrite(output_img_path, img_cv2)

        # Step 3: If 'Dirty', perform YOLO object detection on the image
        if is_dirty:
            results = yolo_model(img_cv2)  # YOLO object detection
            detected_objects = results.pandas().xyxy[0]

            # Step 4: Loop through detected objects and process garbage
            for i, row in detected_objects.iterrows():
                label = row['name']  # Class name (label)
                confidence = row['confidence']
                x1, y1, x2, y2 = int(row['xmin']), int(row['ymin']), int(row['xmax']), int(row['ymax'])

                # Draw bounding box for garbage
                if label in ['garbage', 'waste']:
                    color = (0, 165, 255)  # Orange for garbage
                    cv2.putText(img_cv2, f"Garbage ({confidence:.2f})", (x1, y1 - 10), cv2.FONT_HERSHEY_SIMPLEX, 0.9, color, 2)
                    # Draw the bounding box for the object
                    cv2.rectangle(img_cv2, (x1, y1), (x2, y2), color, 2)

            # Save the dirty image with bounding boxes
            output_img_path = os.path.join("static", f"output_image_{image_id}_dirty.jpg")
            cv2.imwrite(output_img_path, img_cv2)

        return status, output_img_path

    except Exception as e:
        print(f"Error loading or processing image: {e}")
        return "Error", None

@app.route('/', methods=['GET', 'POST'])
def index():
    conn = get_db_connection()
    cursor = conn.cursor(dictionary=True)

    if request.method == 'POST':
        post_office_name = request.form.get('post_office_name')

        # Fetch data from the database
        cursor.execute("SELECT * FROM post WHERE Name = %s", (post_office_name,))
        result = cursor.fetchone()

        if result:
            # Extract details from the result
            cleanliness_status = result['Status']
            latitude = result['Latitude']
            longitude = result['Longitude']
            timestamp = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
            img_path = result['images']  # Assuming this is the path to the processed image

            # Generate a unique ID for each output image
            image_id = datetime.now().strftime('%Y%m%d%H%M%S')

            # Process the image
            status, output_img_path = test_single_image(img_path, image_id)

            # Prepare data for processed_images table
            processed_data = (
                result['Name'],
                output_img_path,
                status,
                timestamp,
                latitude,
                longitude
            )

            # Update the processed_images table
            cursor.execute(""" 
                INSERT INTO processed_images (Name, image_path, cleanliness_status, timestamp, latitude, longitude) 
                VALUES (%s, %s, %s, %s, %s, %s)
                ON DUPLICATE KEY UPDATE 
                image_path = VALUES(image_path), 
                cleanliness_status = VALUES(cleanliness_status), 
                timestamp = VALUES(timestamp), 
                latitude = VALUES(latitude), 
                longitude = VALUES(longitude)
            """, processed_data)

            conn.commit()

            return jsonify({
                'status': status,
                'timestamp': timestamp,
                'latitude': latitude,
                'longitude': longitude,
                'output_image': output_img_path,
            })
        else:
            return jsonify({'status': 'Post office not found.'}), 404

    cursor.execute("SELECT * FROM post")
    post_offices = cursor.fetchall()
    cursor.close()  # Close cursor after use
    conn.close()  # Close connection after use
    return render_template('Live.php', post_offices=post_offices)

if __name__ == "__main__":
    app.run(debug=True)

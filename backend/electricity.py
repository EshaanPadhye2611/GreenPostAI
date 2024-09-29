import pandas as pd
import mysql.connector
import random

# Function to fetch post office data from MySQL database
def fetch_post_office_data(host, database, user, password):
    try:
        # Establishing the connection
        conn = mysql.connector.connect(
            host=host,
            database=database,
            user=user,
            password=password
        )
        
        # Query to fetch data
        query = "SELECT Name, City, State, Latitude, Longitude FROM post"
        
        # Fetching the data into a DataFrame
        df = pd.read_sql(query, conn)
        
        return df
    except Exception as e:
        print(f"Error: {e}")
    finally:
        if conn.is_connected():
            conn.close()

# Function to add garbage data
def add_garbage_data(df):
    # Define categories and frequencies
    garbage_categories = ['Organic', 'Recyclable', 'Non-Recyclable', 'Hazardous']
    frequencies = ['Daily', 'Bi-Weekly', 'Weekly']

    # Add random garbage category and frequency
    df['Garbage_Category'] = [random.choice(garbage_categories) for _ in range(len(df))]
    df['Garbage_Frequency'] = [random.choice(frequencies) for _ in range(len(df))]
    
    # Add average time between detections (in hours)
    df['Average_Time_Between_Detections'] = [random.randint(1, 72) for _ in range(len(df))]  # Random hours between 1 and 72

    # Create weekly data for garbage detection
    weeks = [f'Week_{i+1}' for i in range(4)]  # Assuming 4 weeks for simplicity
    for week in weeks:
        df[f'Garbage_Detections_{week}'] = [random.randint(0, 10) for _ in range(len(df))]  # Random detections

    return df

# Main script
if __name__ == "__main__":
    # Database connection details
    host = 'localhost'
    database = 'post_office'
    user = 'root'
    password = 'astro'
    
    # Fetch post office data
    post_office_data = fetch_post_office_data(host, database, user, password)
    
    if post_office_data is not None:
        # Add garbage data
        post_office_data = add_garbage_data(post_office_data)
        
        # Save to CSV
        post_office_data.to_csv('post_offices_with_garbage_data.csv', index=False)
        
        print("CSV file created successfully: post_offices_with_garbage_data.csv")

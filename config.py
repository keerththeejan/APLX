import os


class Config:
    SECRET_KEY = os.getenv('SECRET_KEY', 'dev-secret-key')

    # Optional universal DB URL override, e.g. "sqlite:///app.db"
    DATABASE_URL = os.getenv('DATABASE_URL')

    DB_USER = os.getenv('DB_USER', 'root')
    DB_PASSWORD = os.getenv('DB_PASSWORD', '')
    DB_HOST = os.getenv('DB_HOST', '127.0.0.1')
    DB_NAME = os.getenv('DB_NAME', 'myapp')

    # Prefer DATABASE_URL if provided; otherwise build MySQL URI
    SQLALCHEMY_DATABASE_URI = (
        DATABASE_URL
        if DATABASE_URL
        else f"mysql+pymysql://{DB_USER}:{DB_PASSWORD}@{DB_HOST}/{DB_NAME}?charset=utf8mb4"
    )
    SQLALCHEMY_TRACK_MODIFICATIONS = False

from flask import Flask, render_template, request, redirect, url_for, flash
from flask_sqlalchemy import SQLAlchemy
from sqlalchemy.exc import SQLAlchemyError
from sqlalchemy import inspect, func
from dotenv import load_dotenv
from zoneinfo import ZoneInfo
import os
import datetime
import random
import string
from werkzeug.utils import secure_filename

# Global SQLAlchemy instance

db = SQLAlchemy()


def create_app():
    """Application factory for the Flask app."""
    # Load .env and allow overriding existing environment variables
    load_dotenv(override=True)

    app = Flask(__name__, static_folder='static', template_folder='templates')
    app.config.from_object('config.Config')
    
    # Normalize SQLite path and ensure directory exists to avoid "unable to open database file"
    try:
        uri = app.config.get('SQLALCHEMY_DATABASE_URI', '') or ''
        if uri.startswith('sqlite:///'):
            rel_path = uri.replace('sqlite:///', '')
            # Make absolute path relative to this file's directory
            if not os.path.isabs(rel_path):
                base_dir = os.path.dirname(os.path.abspath(__file__))
                abs_path = os.path.normpath(os.path.join(base_dir, rel_path))
                # Ensure the parent directory exists
                os.makedirs(os.path.dirname(abs_path), exist_ok=True)
                # Use forward slashes for SQLAlchemy URI compatibility on Windows
                abs_path_uri = abs_path.replace('\\', '/')
                app.config['SQLALCHEMY_DATABASE_URI'] = f"sqlite:///{abs_path_uri}"
            else:
                os.makedirs(os.path.dirname(rel_path), exist_ok=True)
    except Exception:
        # If anything goes wrong here, proceed; SQLAlchemy will raise clearer errors later
        pass

    # Initialize DB
    db.init_app(app)

    # Models
    class Shipment(db.Model):
        __tablename__ = 'shipments'
        id = db.Column(db.Integer, primary_key=True)
        awb = db.Column(db.String(16), unique=True, nullable=False, index=True)
        sender_name = db.Column(db.String(120), nullable=False)
        sender_phone = db.Column(db.String(30), nullable=True)
        receiver_name = db.Column(db.String(120), nullable=False)
        receiver_phone = db.Column(db.String(30), nullable=True)
        origin = db.Column(db.String(120), nullable=False)
        destination = db.Column(db.String(120), nullable=False)
        weight_kg = db.Column(db.Float, nullable=True)
        price = db.Column(db.Float, nullable=True)
        status = db.Column(db.String(50), nullable=False, default='Booked')
        created_at = db.Column(db.DateTime, default=datetime.datetime.utcnow)

        events = db.relationship('TrackingEvent', backref='shipment', cascade='all, delete-orphan', lazy='dynamic')

    class TrackingEvent(db.Model):
        __tablename__ = 'tracking_events'
        id = db.Column(db.Integer, primary_key=True)
        shipment_id = db.Column(db.Integer, db.ForeignKey('shipments.id'), nullable=False)
        status = db.Column(db.String(80), nullable=False)
        location = db.Column(db.String(120), nullable=True)
        note = db.Column(db.String(255), nullable=True)
        created_at = db.Column(db.DateTime, default=datetime.datetime.utcnow, index=True)

    class Notification(db.Model):
        __tablename__ = 'notifications'
        id = db.Column(db.Integer, primary_key=True)
        type = db.Column(db.String(50), nullable=False)  # 'booking', 'shipment', 'message'
        title = db.Column(db.String(200), nullable=False)
        message = db.Column(db.Text, nullable=False)
        is_read = db.Column(db.Boolean, default=False)
        created_at = db.Column(db.DateTime, default=datetime.datetime.utcnow)
        related_id = db.Column(db.Integer, nullable=True)  # ID of related shipment/booking

    class Customer(db.Model):
        __tablename__ = 'customers'
        id = db.Column(db.Integer, primary_key=True)
        first_name = db.Column(db.String(100), nullable=False)
        last_name = db.Column(db.String(100), nullable=False)
        email = db.Column(db.String(120), unique=True, nullable=False, index=True)
        phone = db.Column(db.String(20), nullable=True)
        address = db.Column(db.Text, nullable=True)
        city = db.Column(db.String(100), nullable=True)
        state = db.Column(db.String(100), nullable=True)
        postal_code = db.Column(db.String(20), nullable=True)
        country = db.Column(db.String(100), nullable=True)
        created_at = db.Column(db.DateTime, default=datetime.datetime.utcnow)
        is_active = db.Column(db.Boolean, default=True)

        @property
        def full_name(self):
            return f"{self.first_name} {self.last_name}"

        @property
        def full_address(self):
            parts = [self.address, self.city, self.state, self.postal_code, self.country]
            return ", ".join([part for part in parts if part])

    class Admin(db.Model):
        __tablename__ = 'admin_profile'
        id = db.Column(db.Integer, primary_key=True)
        name = db.Column(db.String(120), nullable=False, default='Admin')
        email = db.Column(db.String(120), nullable=True)
        phone = db.Column(db.String(30), nullable=True)
        company = db.Column(db.String(150), nullable=True)
        address = db.Column(db.Text, nullable=True)
        password_hash = db.Column(db.String(255), nullable=True)
        updated_at = db.Column(db.DateTime, default=datetime.datetime.utcnow, onupdate=datetime.datetime.utcnow)

    class HomeService(db.Model):
        __tablename__ = 'home_services'
        id = db.Column(db.Integer, primary_key=True)
        title = db.Column(db.String(120), nullable=False)
        description = db.Column(db.String(255), nullable=False)
        icon = db.Column(db.String(10), nullable=True)  # simple emoji/icon
        image_url = db.Column(db.String(255), nullable=True)
        display_order = db.Column(db.Integer, nullable=False, default=0)
        active = db.Column(db.Boolean, default=True)
        created_at = db.Column(db.DateTime, default=datetime.datetime.utcnow)

        def to_dict(self):
            return {
                'id': self.id,
                'title': self.title,
                'description': self.description,
                'icon': self.icon,
                'image_url': self.image_url,
                'display_order': self.display_order,
                'active': self.active,
            }

    class HomeFeature(db.Model):
        __tablename__ = 'home_features'
        id = db.Column(db.Integer, primary_key=True)
        title = db.Column(db.String(120), nullable=False)
        description = db.Column(db.String(255), nullable=False)
        icon = db.Column(db.String(10), nullable=True)
        display_order = db.Column(db.Integer, nullable=False, default=0)
        active = db.Column(db.Boolean, default=True)
        created_at = db.Column(db.DateTime, default=datetime.datetime.utcnow)

        def to_dict(self):
            return {
                'id': self.id,
                'title': self.title,
                'description': self.description,
                'icon': self.icon,
                'display_order': self.display_order,
                'active': self.active,
            }

    def get_or_create_admin() -> 'Admin':
        admin = Admin.query.first()
        if not admin:
            admin = Admin(name='Admin')
            db.session.add(admin)
            db.session.commit()
        return admin

    # Utils
    def generate_awb(prefix='PRCL'):
        suffix = ''.join(random.choices(string.digits, k=8))
        return f"{prefix}{suffix}"

    def create_notification(notification_type, title, message, related_id=None):
        """Create a new notification"""
        try:
            notification = Notification(
                type=notification_type,
                title=title,
                message=message,
                related_id=related_id
            )
            db.session.add(notification)
            db.session.commit()
            return notification
        except SQLAlchemyError as e:
            db.session.rollback()
            app.logger.error(f'Error creating notification: {e}')
            return None

    def send_email(to_email: str, subject: str, body: str) -> bool:
        """Send an email using Gmail SMTP. Requires GMAIL_USER and GMAIL_APP_PASSWORD in env.
        Returns True on success, False otherwise.
        """
        import smtplib
        from email.mime.text import MIMEText
        from email.mime.multipart import MIMEMultipart

        user = os.getenv('GMAIL_USER')
        pwd = os.getenv('GMAIL_APP_PASSWORD')
        if not user or not pwd:
            app.logger.warning('GMAIL credentials not configured; skipping email send')
            return False

        try:
            msg = MIMEMultipart()
            msg['From'] = user
            msg['To'] = to_email
            msg['Subject'] = subject
            msg.attach(MIMEText(body, 'plain'))

            with smtplib.SMTP('smtp.gmail.com', 587) as server:
                server.starttls()
                server.login(user, pwd)
                server.send_message(msg)
            return True
        except Exception as e:
            app.logger.error(f'Error sending email to {to_email}: {e}')
            return False

    def get_unread_notifications():
        """Get all unread notifications"""
        return Notification.query.filter_by(is_read=False).order_by(Notification.created_at.desc()).all()

    def get_all_notifications(limit=20):
        """Get notifications with optional limit. If limit is None, return all."""
        q = Notification.query.order_by(Notification.created_at.desc())
        return (q.all() if limit is None else q.limit(limit).all())

    def mark_notification_read(notification_id):
        """Mark a notification as read"""
        try:
            notification = db.session.get(Notification, notification_id)
            if notification:
                notification.is_read = True
                db.session.commit()
                return True
        except SQLAlchemyError as e:
            db.session.rollback()
            app.logger.error(f'Error marking notification as read: {e}')
        return False

    def get_notification_count():
        """Get count of unread notifications"""
        return Notification.query.filter_by(is_read=False).count()

    # Time helpers
    def format_colombo(dt: datetime.datetime | None) -> str:
        """Format a datetime in Asia/Colombo time as 'YYYY-MM-DD HH:MM:SS'.
        If dt is naive, treat it as UTC. Works even if system tzdb is missing.
        """
        if not dt:
            return ''
        # Ensure we treat naive timestamps as UTC
        if dt.tzinfo is None:
            dt = dt.replace(tzinfo=datetime.timezone.utc)
        # Try with ZoneInfo first
        try:
            colombo = dt.astimezone(ZoneInfo('Asia/Colombo'))
            return colombo.strftime('%Y-%m-%d %H:%M:%S')
        except Exception:
            # Fallback: fixed offset +05:30 (IST)
            try:
                ist = datetime.timezone(datetime.timedelta(hours=5, minutes=30))
                colombo = dt.astimezone(ist)
                return colombo.strftime('%Y-%m-%d %H:%M:%S')
            except Exception:
                # Last resort: return naive string (likely UTC)
                return dt.strftime('%Y-%m-%d %H:%M:%S')

    # Currency helpers
    @app.template_filter('lkr')
    def jinja_lkr(value):
        """Format a number as Sri Lankan Rupees, e.g., 'Rs 1,234.50'."""
        try:
            v = float(value or 0)
        except (TypeError, ValueError):
            v = 0.0
        return f"Rs {v:,.2f}"

    # Time filters (Sri Lanka / Asia/Colombo)
    @app.template_filter('colombo')
    def jinja_colombo(dt: datetime.datetime | None) -> str:
        """Format a datetime in Asia/Colombo using default '%Y-%m-%d %H:%M:%S'."""
        return format_colombo(dt)

    @app.template_filter('colombo_human')
    def jinja_colombo_human(dt: datetime.datetime | None) -> str:
        """Format a datetime in Asia/Colombo as 'DD Mon YYYY HH:MM AM/PM'."""
        if not dt:
            return ''
        # Ensure we treat naive timestamps as UTC
        if dt.tzinfo is None:
            dt = dt.replace(tzinfo=datetime.timezone.utc)
        try:
            colombo = dt.astimezone(ZoneInfo('Asia/Colombo'))
            return colombo.strftime('%d %b %Y %I:%M %p')
        except Exception:
            try:
                ist = datetime.timezone(datetime.timedelta(hours=5, minutes=30))
                colombo = dt.astimezone(ist)
                return colombo.strftime('%d %b %Y %I:%M %p')
            except Exception:
                return ''

    # Ensure uploads directory exists
    def ensure_dir(path: str):
        try:
            os.makedirs(path, exist_ok=True)
        except Exception:
            pass

    @app.route('/api/services', methods=['POST'])
    def api_create_service():
        """Create a new HomeService. Accepts multipart/form-data or JSON.
        form fields: title (required), description (required), icon (emoji/text optional), image (file optional)
        """
        try:
            # Accept both JSON and multipart
            if request.content_type and 'application/json' in request.content_type:
                data = request.get_json(silent=True) or {}
                title = (data.get('title') or '').strip()
                description = (data.get('description') or '').strip()
                icon = (data.get('icon') or '').strip() or None
                image_url = None
            else:
                form = request.form
                files = request.files
                title = (form.get('title') or '').strip()
                description = (form.get('description') or '').strip()
                icon = (form.get('icon') or '').strip() or None
                image_url = None
                if 'image' in files and files['image'] and files['image'].filename:
                    img = files['image']
                    filename = secure_filename(img.filename)
                    upload_dir = os.path.join(app.static_folder, 'uploads', 'services')
                    ensure_dir(upload_dir)
                    save_path = os.path.join(upload_dir, filename)
                    img.save(save_path)
                    # public URL
                    image_url = url_for('static', filename=f'uploads/services/{filename}')

            if not title or not description:
                return {'success': False, 'error': 'Title and description are required'}, 400

            svc = HomeService(title=title, description=description, icon=icon, image_url=image_url, display_order=0, active=True)
            db.session.add(svc)
            db.session.commit()

            # Create a notification for visibility
            create_notification('message', 'Service Added', f'New service "{title}" has been added', svc.id)

            return {'success': True, 'service': svc.to_dict()}
        except SQLAlchemyError as e:
            db.session.rollback()
            return {'success': False, 'error': str(e)}, 500

    @app.route('/api/customers/<int:customer_id>', methods=['GET', 'PUT', 'DELETE'])
    def customer_detail(customer_id: int):
        """Retrieve, update, or delete a single customer.
        - GET returns customer data
        - PUT updates provided fields (partial update supported)
        - DELETE removes the record
        """
        c = db.session.get(Customer, customer_id)
        if not c:
            return {'success': False, 'error': 'Not found'}, 404

        if request.method == 'GET':
            return {
                'success': True,
                'customer': {
                    'id': c.id,
                    'first_name': c.first_name,
                    'last_name': c.last_name,
                    'full_name': c.full_name,
                    'email': c.email,
                    'phone': c.phone,
                    'address': c.address,
                    'city': c.city,
                    'state': c.state,
                    'postal_code': c.postal_code,
                    'country': c.country,
                    'is_active': c.is_active,
                    'created_at': c.created_at.strftime('%Y-%m-%d %H:%M:%S'),
                }
            }

        if request.method == 'DELETE':
            try:
                db.session.delete(c)
                db.session.commit()
                return {'success': True}
            except SQLAlchemyError as e:
                db.session.rollback()
                return {'success': False, 'error': str(e)}, 500

        # PUT
        data = request.get_json(silent=True) or {}

        # Optional unique email check if changing
        new_email = data.get('email')
        if isinstance(new_email, str):
            new_email_norm = new_email.strip().lower()
            if new_email_norm and new_email_norm != (c.email or '').lower():
                if Customer.query.filter(Customer.email == new_email_norm, Customer.id != c.id).first():
                    return {'success': False, 'error': 'Email already exists'}, 409
                c.email = new_email_norm

        # Generic string fields
        for field in ['first_name', 'last_name', 'phone', 'address', 'city', 'state', 'postal_code', 'country']:
            if field in data:
                val = (data.get(field) or '').strip()
                setattr(c, field, val or None)

        # Boolean status parsing
        if 'is_active' in data:
            raw_active = data.get('is_active')
            if isinstance(raw_active, bool):
                c.is_active = raw_active
            elif isinstance(raw_active, (int, float)):
                c.is_active = int(raw_active) != 0
            elif isinstance(raw_active, str):
                v = raw_active.strip().lower()
                c.is_active = v in ('true', '1', 'yes', 'y')

        try:
            db.session.commit()
            return {'success': True}
        except SQLAlchemyError as e:
            db.session.rollback()
            return {'success': False, 'error': str(e)}, 500
        except Exception as e:
            return {'success': False, 'error': str(e)}, 500

    # ------------------------------
    # Home Services CRUD (Content Manager)
    # ------------------------------
    @app.route('/api/home/services', methods=['GET'])
    def api_home_services_list():
        try:
            items = HomeService.query.order_by(HomeService.display_order.asc(), HomeService.id.asc()).all()
            return { 'items': [s.to_dict() for s in items] }
        except Exception as e:
            return { 'items': [], 'error': str(e) }, 500

    @app.route('/api/home/services', methods=['POST'])
    def api_home_services_create():
        data = request.get_json(silent=True) or {}
        title = (data.get('title') or '').strip()
        description = (data.get('description') or '').strip()
        if not title or not description:
            return { 'success': False, 'error': 'Title and description are required' }, 400
        try:
            s = HomeService(
                title=title,
                description=description,
                icon=(data.get('icon') or '').strip() or None,
                image_url=(data.get('image_url') or '').strip() or None,
                display_order=int(data.get('display_order') or 0),
                active=bool(data.get('active')),
            )
            db.session.add(s)
            db.session.commit()
            return { 'success': True, 'item': s.to_dict() }
        except SQLAlchemyError as e:
            db.session.rollback()
            return { 'success': False, 'error': str(e) }, 500

    @app.route('/api/home/services/<int:item_id>', methods=['PUT'])
    def api_home_services_update(item_id):
        data = request.get_json(silent=True) or {}
        try:
            s = db.session.get(HomeService, item_id)
            if not s:
                return { 'success': False, 'error': 'Not found' }, 404
            def val(key, cast=str):
                v = data.get(key)
                if v is None:
                    return None
                try:
                    return cast(v)
                except Exception:
                    return None
            if 'title' in data: s.title = (val('title') or s.title)
            if 'description' in data: s.description = (val('description') or s.description)
            if 'icon' in data: s.icon = (val('icon') or None)
            if 'image_url' in data: s.image_url = (val('image_url') or None)
            if 'display_order' in data:
                try:
                    s.display_order = int(data.get('display_order') or 0)
                except Exception:
                    pass
            if 'active' in data: s.active = bool(data.get('active'))
            db.session.commit()
            return { 'success': True }
        except SQLAlchemyError as e:
            db.session.rollback()
            return { 'success': False, 'error': str(e) }, 500

    @app.route('/api/home/services/<int:item_id>', methods=['DELETE'])
    def api_home_services_delete(item_id):
        try:
            s = db.session.get(HomeService, item_id)
            if not s:
                return { 'success': False, 'error': 'Not found' }, 404
            db.session.delete(s)
            db.session.commit()
            return { 'success': True }
        except SQLAlchemyError as e:
            db.session.rollback()
            return { 'success': False, 'error': str(e) }, 500

    # ------------------------------
    # Home Features CRUD (Content Manager)
    # ------------------------------
    @app.route('/api/home/features', methods=['GET'])
    def api_home_features_list():
        try:
            items = HomeFeature.query.order_by(HomeFeature.display_order.asc(), HomeFeature.id.asc()).all()
            return { 'items': [f.to_dict() for f in items] }
        except Exception as e:
            return { 'items': [], 'error': str(e) }, 500

    @app.route('/api/home/features', methods=['POST'])
    def api_home_features_create():
        data = request.get_json(silent=True) or {}
        title = (data.get('title') or '').strip()
        description = (data.get('description') or '').strip()
        if not title or not description:
            return { 'success': False, 'error': 'Title and description are required' }, 400
        try:
            f = HomeFeature(
                title=title,
                description=description,
                icon=(data.get('icon') or '').strip() or None,
                display_order=int(data.get('display_order') or 0),
                active=bool(data.get('active')),
            )
            db.session.add(f)
            db.session.commit()
            return { 'success': True, 'item': f.to_dict() }
        except SQLAlchemyError as e:
            db.session.rollback()
            return { 'success': False, 'error': str(e) }, 500

    @app.route('/api/home/features/<int:item_id>', methods=['PUT'])
    def api_home_features_update(item_id):
        data = request.get_json(silent=True) or {}
        try:
            f = db.session.get(HomeFeature, item_id)
            if not f:
                return { 'success': False, 'error': 'Not found' }, 404
            if 'title' in data: f.title = (data.get('title') or f.title)
            if 'description' in data: f.description = (data.get('description') or f.description)
            if 'icon' in data: f.icon = (data.get('icon') or None)
            if 'display_order' in data:
                try:
                    f.display_order = int(data.get('display_order') or 0)
                except Exception:
                    pass
            if 'active' in data: f.active = bool(data.get('active'))
            db.session.commit()
            return { 'success': True }
        except SQLAlchemyError as e:
            db.session.rollback()
            return { 'success': False, 'error': str(e) }, 500

    @app.route('/api/home/features/<int:item_id>', methods=['DELETE'])
    def api_home_features_delete(item_id):
        try:
            f = db.session.get(HomeFeature, item_id)
            if not f:
                return { 'success': False, 'error': 'Not found' }, 404
            db.session.delete(f)
            db.session.commit()
            return { 'success': True }
        except SQLAlchemyError as e:
            db.session.rollback()
            return { 'success': False, 'error': str(e) }, 500
    @app.route('/api/upload', methods=['POST'])
    def api_upload():
        """Generic upload endpoint for images. Accepts multipart with fields:
        - file: the file to upload (required)
        - folder: target subfolder under static/uploads (e.g., 'services', 'features'). Defaults to 'misc'.
        Returns a public URL.
        """
        try:
            if 'file' not in request.files:
                return {'success': False, 'error': 'No file provided'}, 400
            f = request.files['file']
            if not f or not f.filename:
                return {'success': False, 'error': 'Empty filename'}, 400
            folder = (request.form.get('folder') or 'misc').strip().lower()
            # allowlist
            if folder not in {'services','features','misc'}:
                folder = 'misc'
            filename = secure_filename(f.filename)
            upload_dir = os.path.join(app.static_folder, 'uploads', folder)
            ensure_dir(upload_dir)
            save_path = os.path.join(upload_dir, filename)
            f.save(save_path)
            url = url_for('static', filename=f'uploads/{folder}/{filename}')
            return {'success': True, 'url': url}
        except Exception as e:
            return {'success': False, 'error': str(e)}, 500

    # Create tables if they don't exist
    with app.app_context():
        db.create_all()
        # Ensure new columns exist (dialect-aware)
        try:
            dialect = db.engine.dialect.name
            inspector = inspect(db.engine)
            # Admin.password_hash
            admin_cols = {col['name'] for col in inspector.get_columns('admin_profile')}
            if 'password_hash' not in admin_cols:
                if dialect.startswith('mysql'):
                    db.session.execute(db.text("ALTER TABLE admin_profile ADD COLUMN password_hash VARCHAR(255)"))
                else:
                    db.session.execute(db.text("ALTER TABLE admin_profile ADD COLUMN password_hash TEXT"))
                db.session.commit()
        except Exception as e:
            app.logger.warning(f"Admin schema migration warning: {e}")
        # Shipments: add paid_amount if missing (for due calculations)
        try:
            dialect = db.engine.dialect.name
            inspector = inspect(db.engine)
            ship_cols = {col['name'] for col in inspector.get_columns('shipments')}
            if 'paid_amount' not in ship_cols:
                if dialect.startswith('mysql'):
                    db.session.execute(db.text("ALTER TABLE shipments ADD COLUMN paid_amount FLOAT DEFAULT 0"))
                else:
                    db.session.execute(db.text("ALTER TABLE shipments ADD COLUMN paid_amount REAL DEFAULT 0"))
                db.session.commit()
        except Exception as e:
            app.logger.warning(f"Shipments schema migration warning: {e}")
        
        # Add sample customer data if no customers exist
        if Customer.query.count() == 0:
            sample_customers = [
                Customer(
                    first_name='John',
                    last_name='Doe',
                    email='john.doe@example.com',
                    phone='+1234567890',
                    address='123 Main Street',
                    city='New York',
                    state='NY',
                    postal_code='10001',
                    country='USA'
                ),
                Customer(
                    first_name='Jane',
                    last_name='Smith',
                    email='jane.smith@example.com',
                    phone='+1987654321',
                    address='456 Oak Avenue',
                    city='Los Angeles',
                    state='CA',
                    postal_code='90210',
                    country='USA'
                ),
                Customer(
                    first_name='Raj',
                    last_name='Kumar',
                    email='raj.kumar@example.com',
                    phone='+919876543210',
                    address='789 Park Road',
                    city='Mumbai',
                    state='Maharashtra',
                    postal_code='400001',
                    country='India'
                )
            ]
            
            for customer in sample_customers:
                db.session.add(customer)
            
            try:
                db.session.commit()
                print("Sample customer data added successfully!")
            except Exception as e:
                db.session.rollback()
                print(f"Error adding sample data: {e}")

        # Seed default home services/features
        try:
            if HomeService.query.count() == 0:
                defaults = [
                    HomeService(title='Air Freight', description='Efficient and reliable air freight solutions for your business needs.', icon='✈️', display_order=1),
                    HomeService(title='Ocean Freight', description='Comprehensive ocean freight services worldwide.', icon='🚢', display_order=2),
                    HomeService(title='Land Transport', description='Efficient land transportation solutions for all your needs.', icon='🚚', display_order=3),
                ]
                db.session.add_all(defaults)
                db.session.commit()
            if HomeFeature.query.count() == 0:
                feats = [
                    HomeFeature(title='Fast & Secure Delivery', description='Timely and secure delivery of your goods', icon='🚚', display_order=1),
                    HomeFeature(title='Global Network', description='Worldwide logistics solutions', icon='🌍', display_order=2),
                    HomeFeature(title='24/7 Support', description='Round the clock customer support', icon='🕒', display_order=3),
                    HomeFeature(title='Certified Company', description='Fully licensed and insured', icon='✅', display_order=4),
                ]
                db.session.add_all(feats)
                db.session.commit()
        except Exception as e:
            db.session.rollback()
            print(f"Error seeding home content: {e}")

    app.logger.info(f"SQLALCHEMY_DATABASE_URI -> {app.config.get('SQLALCHEMY_DATABASE_URI')}")

    @app.route('/', methods=['GET', 'POST'])
    def index():
        # Book a parcel
        if request.method == 'POST':
            form = request.form
            required = ['sender_name', 'receiver_name', 'origin', 'destination']
            missing = [k for k in required if not form.get(k, '').strip()]
            if missing:
                flash(f"Missing fields: {', '.join(missing)}", 'error')
            else:
                try:
                    awb = generate_awb()
                    shipment = Shipment(
                        awb=awb,
                        sender_name=form.get('sender_name').strip(),
                        sender_phone=form.get('sender_phone', '').strip() or None,
                        receiver_name=form.get('receiver_name').strip(),
                        receiver_phone=form.get('receiver_phone', '').strip() or None,
                        origin=form.get('origin').strip(),
                        destination=form.get('destination').strip(),
                        weight_kg=float(form.get('weight_kg')) if form.get('weight_kg') else None,
                        price=float(form.get('price')) if form.get('price') else None,
                        status='Booked',
                    )
                    db.session.add(shipment)
                    db.session.commit()
                    
                    # Create notification for new booking
                    create_notification(
                        'booking',
                        'New Shipment Booked',
                        f'New shipment from {form.get("sender_name")} to {form.get("receiver_name")}. AWB: {awb}',
                        shipment.id
                    )
                    
                    flash(f'Shipment booked. AWB: {awb}', 'success')
                    return redirect(url_for('index'))
                except SQLAlchemyError as e:
                    db.session.rollback()
                    flash(f'Error booking shipment: {str(e)}', 'error')

        # Load dynamic home content
        services = HomeService.query.filter_by(active=True).order_by(HomeService.display_order.asc(), HomeService.id.asc()).all()
        features = HomeFeature.query.filter_by(active=True).order_by(HomeFeature.display_order.asc(), HomeFeature.id.asc()).all()
        shipments = Shipment.query.order_by(Shipment.created_at.desc()).all()
        return render_template('index.html', shipments=shipments, services=services, features=features)

    @app.route('/book', methods=['GET', 'POST'])
    def book_page():
        if request.method == 'POST':
            form = request.form
            required = ['sender_name', 'receiver_name', 'origin', 'destination']
            missing = [k for k in required if not form.get(k, '').strip()]
            if missing:
                flash(f"Missing fields: {', '.join(missing)}", 'error')
            else:
                try:
                    awb = generate_awb()
                    shipment = Shipment(
                        awb=awb,
                        sender_name=form.get('sender_name').strip(),
                        sender_phone=form.get('sender_phone', '').strip() or None,
                        receiver_name=form.get('receiver_name').strip(),
                        receiver_phone=form.get('receiver_phone', '').strip() or None,
                        origin=form.get('origin').strip(),
                        destination=form.get('destination').strip(),
                        weight_kg=float(form.get('weight_kg')) if form.get('weight_kg') else None,
                        price=float(form.get('price')) if form.get('price') else None,
                        status='Booked',
                    )
                    db.session.add(shipment)
                    db.session.commit()
                    create_notification('booking', 'New Shipment Booked', f"New shipment from {form.get('sender_name')} to {form.get('receiver_name')}. AWB: {awb}", shipment.id)
                    flash(f'Shipment booked. AWB: {awb}', 'success')
                    return redirect(url_for('book_page'))
                except SQLAlchemyError as e:
                    db.session.rollback()
                    flash(f'Error booking shipment: {str(e)}', 'error')
        return render_template('book.html', hide_nav=True)

    @app.route('/register', methods=['GET', 'POST'])
    def register():
        if request.method == 'POST':
            form = request.form
            required = ['first_name', 'last_name', 'email', 'phone', 'address', 'city', 'state', 'postal_code', 'country']
            missing = [k for k in required if not form.get(k, '').strip()]
            
            if missing:
                flash(f"Missing required fields: {', '.join(missing)}", 'error')
            elif not form.get('terms_accepted'):
                flash('You must accept the Terms and Conditions to register', 'error')
            else:
                # Duplicate email check first for a clear user message
                existing = Customer.query.filter_by(email=form.get('email').strip()).first()
                if existing:
                    if existing.is_active:
                        flash('This email is already registered and approved. Please use the existing account or contact support.', 'warning')
                    else:
                        flash('This email has already been registered and is awaiting admin approval.', 'warning')
                    return redirect(url_for('register'))
                
                # Save customer to database
                try:
                    customer = Customer(
                        first_name=form.get('first_name').strip(),
                        last_name=form.get('last_name').strip(),
                        email=form.get('email').strip(),
                        phone=form.get('phone', '').strip() or None,
                        address=form.get('address', '').strip() or None,
                        city=form.get('city', '').strip() or None,
                        state=form.get('state', '').strip() or None,
                        postal_code=form.get('postal_code', '').strip() or None,
                        country=form.get('country', '').strip() or None,
                        is_active=False  # Awaiting admin approval
                    )
                    db.session.add(customer)
                    db.session.commit()
                    
                    # Create notification for new customer registration
                    create_notification(
                        'message',
                        'New Customer Registration',
                        f'New customer {customer.full_name} has registered with email: {customer.email}',
                        customer.id
                    )
                    
                    name = f"{form.get('first_name')} {form.get('last_name')}"
                    flash(f'Registration successful! Welcome {name}!', 'success')
                    return redirect(url_for('index'))
                except SQLAlchemyError as e:
                    db.session.rollback()
                    flash(f'Registration failed: Email already exists or database error', 'error')
        
        return render_template('register.html')

    @app.route('/contact')
    def contact():
        return render_template('contact.html')

    @app.route('/dashboard')
    def dashboard():
        # Dynamic stats
        try:
            total_users = Customer.query.count()
            total_shipments = Shipment.query.count()
            active_bookings = Shipment.query.filter(~Shipment.status.in_(['Delivered', 'Cancelled'])).count()
            revenue = db.session.query(func.coalesce(func.sum(Shipment.price), 0.0)).scalar() or 0.0
        except Exception:
            total_users = 0
            total_shipments = 0
            active_bookings = 0
            revenue = 0.0

        # Recent notifications (latest 5)
        try:
            recent_notifications = get_all_notifications(limit=5)
        except Exception:
            recent_notifications = []

        stats = {
            'total_users': total_users,
            'total_shipments': total_shipments,
            'active_bookings': active_bookings,
            'revenue': float(revenue or 0.0),
        }
        return render_template('dashboard.html', stats=stats, recent_notifications=recent_notifications)

    @app.route('/dashboard/contact')
    def dashboard_contact():
        """Admin-only Contact page with Add Service/Add Feature forms."""
        return render_template('contact_admin.html', hide_nav=True)

    @app.route('/api/contact-message', methods=['POST'])
    def api_contact_message():
        """Accept contact/quote messages and create a notification for admin."""
        # Accept both JSON and form-encoded
        data = request.get_json(silent=True) or request.form.to_dict() or {}
        name = (data.get('name') or '').strip()
        email = (data.get('email') or '').strip()
        subject = (data.get('subject') or '').strip()
        service = (data.get('service') or '').strip()
        message_text = (data.get('message') or '').strip()

        # Basic validation
        if not name or not email or not subject or not message_text:
            return {'success': False, 'error': 'Name, email, subject and message are required'}, 400

        # Build a concise notification message
        parts = [
            f"From: {name} <{email}>",
            f"Subject: {subject}",
        ]
        if service:
            parts.append(f"Service: {service}")
        # Limit message preview length to keep notification compact
        preview = (message_text[:120] + '…') if len(message_text) > 120 else message_text
        parts.append(f"Message: {preview}")
        notif_msg = " | ".join(parts)

        notif = create_notification(
            'message',
            'New Customer Message',
            notif_msg,
            None
        )

        if not notif:
            return {'success': False, 'error': 'Failed to create notification'}, 500

        return {'success': True}

    @app.route('/admin/profile')
    def admin_profile():
        admin = get_or_create_admin()
        admin_data = {
            'name': admin.name or '',
            'email': admin.email or '',
            'phone': admin.phone or '',
            'company': admin.company or '',
            'address': admin.address or '',
            'updated_at': format_colombo(admin.updated_at)
        }
        return render_template('admin_profile.html', admin=admin_data)

    @app.route('/api/admin/profile', methods=['GET'])
    def api_get_admin_profile():
        admin = get_or_create_admin()
        return {
            'name': admin.name or '',
            'email': admin.email or '',
            'phone': admin.phone or '',
            'company': admin.company or '',
            'address': admin.address or '',
            'updated_at': format_colombo(admin.updated_at)
        }

    @app.route('/api/admin/profile', methods=['POST'])
    def api_update_admin_profile():
        data = request.get_json(silent=True) or {}
        name = (data.get('name') or '').strip()
        if not name:
            return {'success': False, 'error': 'Name is required'}, 400
        email = (data.get('email') or '').strip()
        phone = (data.get('phone') or '').strip()
        company = (data.get('company') or '').strip()
        address = (data.get('address') or '').strip()
        password = (data.get('password') or '').strip()

        try:
            admin = get_or_create_admin()
            admin.name = name
            admin.email = email or None
            admin.phone = phone or None
            admin.company = company or None
            admin.address = address or None
            # If a non-empty password is provided, update the password hash
            if password:
                try:
                    from werkzeug.security import generate_password_hash
                    admin.password_hash = generate_password_hash(password)
                except Exception as e:
                    app.logger.error(f"Failed to hash admin password: {e}")
                    return {'success': False, 'error': 'Failed to set password'}, 500
            # Always bump the updated_at timestamp on save
            try:
                admin.updated_at = datetime.datetime.utcnow()
            except Exception:
                pass
            db.session.commit()

            return {
                'success': True,
                'admin': {
                    'name': admin.name or '',
                    'email': admin.email or '',
                    'phone': admin.phone or '',
                    'company': admin.company or '',
                    'address': admin.address or '',
                    'updated_at': format_colombo(admin.updated_at)
                }
            }
        except SQLAlchemyError as e:
            db.session.rollback()
            return {'success': False, 'error': str(e)}, 500

    # Analytics summary API used by templates/analytics.html
    @app.route('/api/analytics/summary', methods=['GET'])
    def api_analytics_summary():
        """Return shipments/bookings series and totals for the last 12 months from DB."""
        try:
            now = datetime.datetime.utcnow().replace(day=1, hour=0, minute=0, second=0, microsecond=0)
            # Build last 12 month buckets oldest->newest
            months = [ (now - datetime.timedelta(days=30*i)) for i in range(11, -1, -1) ]
            # Normalize to first-of-month
            months = [ m.replace(day=1) for m in months ]

            # Precompute month boundaries
            boundaries = []
            for m in months:
                # next month
                if m.month == 12:
                    nxt = m.replace(year=m.year+1, month=1)
                else:
                    nxt = m.replace(month=m.month+1)
                boundaries.append((m, nxt))

            # Query counts per month
            shipments_series = []
            bookings_series = []
            for start, end in boundaries:
                cnt = Shipment.query.filter(Shipment.created_at >= start, Shipment.created_at < end).count()
                shipments_series.append(cnt)
                # If you add a separate Booking model later, compute from it; for now mirror shipments
                bookings_series.append(cnt)

            # Totals
            total_shipments = Shipment.query.count()
            total_bookings = total_shipments  # placeholder until Booking model exists
            total_revenue = db.session.query(func.coalesce(func.sum(Shipment.price), 0.0)).scalar() or 0.0

            payload = {
                "totals": { "shipments": total_shipments, "bookings": total_bookings, "revenue": float(total_revenue) },
                "series": { "shipments": shipments_series, "bookings": bookings_series }
            }
            return payload
        except Exception as e:
            app.logger.error(f"analytics summary error: {e}")
            # Provide safe fallback so UI can still render
            return {
                "totals": { "shipments": 0, "bookings": 0, "revenue": 0.0 },
                "series": { "shipments": [0]*12, "bookings": [0]*12 }
            }

    # Generic notifications list API used by dropdowns (supports filters)
    @app.route('/api/notifications', methods=['GET'])
    def api_notifications():
        """Return notifications with optional filters:
        - only: 'unread' to filter unread only
        - limit: integer count of items to return (default 10)
        - max_age_hours: if provided, only return items created within this many hours
        Response includes unread_count, notifications, and each item has created_at_human & created_at_iso.
        """
        try:
            only = (request.args.get('only') or '').lower()
            try:
                limit = int(request.args.get('limit') or 10)
            except Exception:
                limit = 10
            try:
                max_age_hours = int(request.args.get('max_age_hours')) if request.args.get('max_age_hours') else None
            except Exception:
                max_age_hours = None

            q = Notification.query
            if only == 'unread':
                q = q.filter_by(is_read=False)
            if max_age_hours is not None:
                cutoff = datetime.datetime.utcnow() - datetime.timedelta(hours=max_age_hours)
                q = q.filter(Notification.created_at >= cutoff)
            q = q.order_by(Notification.created_at.desc())
            items = (q.limit(limit).all() if limit else q.all())

            def serialize(n: Notification):
                return {
                    'id': n.id,
                    'type': n.type,
                    'title': n.title,
                    'message': n.message,
                    'is_read': n.is_read,
                    'created_at': n.created_at.isoformat() if n.created_at else None,
                    'created_at_iso': n.created_at.isoformat() if n.created_at else None,
                    'created_at_human': format_colombo(n.created_at),
                }

            unread_count = get_notification_count()
            return { 'unread_count': unread_count, 'notifications': [serialize(n) for n in items] }
        except Exception as e:
            app.logger.error(f"notifications list error: {e}")
            return { 'unread_count': 0, 'notifications': [] }

    @app.route('/api/customers/<int:customer_id>', methods=['GET'])
    def get_customer(customer_id):
        """API endpoint to get a customer's details"""
        try:
            customer = db.session.get(Customer, customer_id)
            if not customer:
                return {'success': False, 'error': 'Customer not found'}, 404
            created = customer.created_at or datetime.datetime.utcnow()
            return {
                'success': True,
                'id': customer.id,
                'first_name': customer.first_name,
                'last_name': customer.last_name,
                'full_name': customer.full_name,
                'email': customer.email,
                'phone': customer.phone,
                'address': customer.address,
                'city': customer.city,
                'state': customer.state,
                'postal_code': customer.postal_code,
                'country': customer.country,
                'full_address': customer.full_address,
                'is_active': customer.is_active,
                'created_date': created.strftime('%d %b %Y'),
                'created_time': created.strftime('%H:%M')
            }
        except SQLAlchemyError as e:
            return {'success': False, 'error': str(e)}, 500

    # Settings page
    @app.route('/admin/settings')
    def settings():
        return render_template('settings.html')

    @app.route('/api/customers/<int:customer_id>', methods=['PUT'])
    def update_customer(customer_id):
        """API endpoint to update a customer"""
        data = request.get_json(silent=True) or {}
        try:
            customer = db.session.get(Customer, customer_id)
            if not customer:
                return {'success': False, 'error': 'Customer not found'}, 404

            # Assign fields if present
            def val(key):
                v = data.get(key)
                if v is None:
                    return None
                v = str(v).strip()
                return v or None

            if 'first_name' in data: customer.first_name = val('first_name') or customer.first_name
            if 'last_name' in data: customer.last_name = val('last_name') or customer.last_name
            if 'email' in data:
                new_email = (data.get('email') or '').strip().lower()
                if new_email and new_email != customer.email:
                    # ensure uniqueness
                    if Customer.query.filter(Customer.email == new_email, Customer.id != customer.id).first():
                        return {'success': False, 'error': 'Email already exists'}, 409
                    customer.email = new_email
            if 'phone' in data: customer.phone = val('phone')
            if 'address' in data: customer.address = val('address')
            if 'city' in data: customer.city = val('city')
            if 'state' in data: customer.state = val('state')
            if 'postal_code' in data: customer.postal_code = val('postal_code')
            if 'country' in data: customer.country = val('country')
            if 'is_active' in data: customer.is_active = bool(data.get('is_active'))

            db.session.commit()

            create_notification(
                'message',
                'Customer Updated',
                f'Customer {customer.full_name} (ID: {customer.id}) was updated',
                customer.id
            )

            return {'success': True}
        except SQLAlchemyError as e:
            db.session.rollback()
            return {'success': False, 'error': str(e)}, 500

    @app.route('/api/customers/<int:customer_id>', methods=['DELETE'])
    def delete_customer(customer_id):
        """API endpoint to delete a customer"""
        try:
            customer = db.session.get(Customer, customer_id)
            if not customer:
                return {'success': False, 'error': 'Customer not found'}, 404
            name = customer.full_name
            db.session.delete(customer)
            db.session.commit()

            # Notify about deletion
            create_notification(
                'message',
                'Customer Deleted',
                f'Customer {name} (ID: {customer_id}) was deleted',
                customer_id
            )

            return {'success': True}
        except SQLAlchemyError as e:
            db.session.rollback()
            return {'success': False, 'error': str(e)}, 500

    @app.route('/notifications')
    def notifications_page():
        """Full notifications page showing read and unread notifications"""
        notifications = get_all_notifications(limit=None)
        return render_template('notifications.html', notifications=notifications)

    # Analytics page
    @app.route('/analytics')
    def analytics_page():
        return render_template('analytics.html', hide_nav=True)

    @app.route('/api/analytics')
    def api_analytics():
        """Return analytics data for charts.
        Query params:
         - range: 'week' (default), 'year'
        """
        rng = (request.args.get('range') or 'week').lower()

        try:
            now = datetime.datetime.utcnow()
            data = {}

            # Weekly bar (last 7 days): profit as sum(price)
            start_week = now - datetime.timedelta(days=6)
            daily = {}
            for i in range(7):
                d = (start_week + datetime.timedelta(days=i)).date()
                daily[d] = 0.0
            week_shipments = db.session.query(db.text('*')).select_from(Shipment).filter(
                Shipment.created_at >= start_week.replace(hour=0, minute=0, second=0, microsecond=0)
            ).all()
            # Convert rows back via ORM fetch to avoid raw text
            week_shipments = Shipment.query.filter(Shipment.created_at >= start_week.replace(hour=0, minute=0, second=0, microsecond=0)).all()
            for s in week_shipments:
                d = (s.created_at or now).date()
                if d in daily:
                    daily[d] += float(s.price or 0)
            data['week_bar'] = {
                'labels': [d.strftime('%a') for d in daily.keys()],
                'profits': [round(v, 2) for v in daily.values()],
            }

            # Due pie for last 7 days: paid vs due (price - paid_amount)
            paid_total = 0.0
            due_total = 0.0
            for s in week_shipments:
                price = float(s.price or 0)
                paid = float(getattr(s, 'paid_amount', 0) or 0)
                paid_total += min(paid, price)
                due_total += max(price - paid, 0)
            data['due_pie'] = {
                'labels': ['Paid', 'Due'],
                'values': [round(paid_total, 2), round(due_total, 2)]
            }

            # Year line: monthly profit for current year (sum of price)
            year_start = datetime.datetime(now.year, 1, 1)
            month_values = [0.0] * 12
            year_shipments = Shipment.query.filter(Shipment.created_at >= year_start).all()
            for s in year_shipments:
                dt = s.created_at or now
                m = dt.month - 1
                month_values[m] += float(s.price or 0)
            data['year_line'] = {
                'labels': ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
                'profits': [round(v,2) for v in month_values]
            }

            return {'success': True, 'data': data}
        except Exception as e:
            app.logger.error(f"Analytics error: {e}")
            return {'success': False, 'error': str(e)}, 500

    @app.route('/admin/content')
    def admin_content_manager():
        return render_template('admin_content.html')

    @app.route('/api/notifications')
    def get_notifications():
        """API endpoint to get notifications.
        Query params:
          - only=unread -> return only unread notifications for the list
          - limit=<int> -> limit number of results (optional)
          - max_age_hours=<int> -> only include notifications created within last N hours (optional)
        """
        only_param = request.args.get('only', '').lower()
        limit_param = request.args.get('limit', type=int)
        max_age_hours = request.args.get('max_age_hours', type=int)

        # Base queryset
        if only_param == 'unread':
            base_q = Notification.query.filter_by(is_read=False)
        else:
            base_q = Notification.query

        # Age filter
        if max_age_hours and max_age_hours > 0:
            cutoff = datetime.datetime.now(datetime.UTC) - datetime.timedelta(hours=max_age_hours)
            base_q = base_q.filter(Notification.created_at >= cutoff)

        # Order newest first
        base_q = base_q.order_by(Notification.created_at.desc())

        # Limit
        notifications = (base_q.limit(limit_param).all() if limit_param else base_q.all())
        unread_count = get_notification_count()
        
        def humanize_time(dt: datetime.datetime) -> str:
            try:
                now = datetime.datetime.utcnow()
                diff = now - dt
                seconds = int(diff.total_seconds())
                if seconds < 60:
                    return 'just now'
                minutes = seconds // 60
                if minutes < 60:
                    return f"{minutes}m ago"
                hours = minutes // 60
                if hours < 24:
                    return f"{hours}h ago"
                days = hours // 24
                return f"{days}d ago"
            except Exception:
                return dt.strftime('%Y-%m-%d %H:%M')

        notifications_data = []
        for notification in notifications:
            created_iso = notification.created_at.replace(microsecond=0).isoformat() + 'Z'
            notifications_data.append({
                'id': notification.id,
                'type': notification.type,
                'title': notification.title,
                'message': notification.message,
                'is_read': notification.is_read,
                'created_at': notification.created_at.strftime('%Y-%m-%d %H:%M:%S'),
                'created_at_iso': created_iso,
                'created_at_human': humanize_time(notification.created_at),
                'related_id': notification.related_id
            })
        
        return {
            'notifications': notifications_data,
            'unread_count': unread_count
        }

    @app.route('/api/notifications/<int:notification_id>/read', methods=['POST'])
    def mark_notification_as_read(notification_id):
        """API endpoint to mark notification as read"""
        success = mark_notification_read(notification_id)
        return {'success': success}

    @app.route('/api/notifications/mark-all-read', methods=['POST'])
    def mark_all_notifications_read():
        """API endpoint to mark all notifications as read"""
        try:
            Notification.query.filter_by(is_read=False).update({'is_read': True})
            db.session.commit()
            return {'success': True}
        except SQLAlchemyError as e:
            db.session.rollback()
            return {'success': False, 'error': str(e)}

    @app.route('/api/health/db')
    def db_health():
        """Health check for database connectivity and basic table counts.
        Returns 200 with ok=true when DB is reachable; otherwise 500 with error.
        """
        try:
            # Ensure metadata is created (idempotent if already exists)
            db.create_all()

            # Simple connectivity check
            db.session.execute(db.text('SELECT 1'))

            # Collect counts (wrapped individually to avoid failing whole endpoint)
            def safe_count(model):
                try:
                    return model.query.count()
                except Exception:
                    return None

            counts = {
                'shipments': safe_count(Shipment),
                'tracking_events': safe_count(TrackingEvent),
                'notifications': safe_count(Notification),
                'customers': safe_count(Customer),
                'admin_profile': safe_count(Admin),
            }

            return {
                'ok': True,
                'driver': 'mysql+pymysql' if 'pymysql' in app.config.get('SQLALCHEMY_DATABASE_URI', '') else 'unknown',
                'db_host': app.config.get('DB_HOST'),
                'db_name': app.config.get('DB_NAME'),
                'counts': counts,
            }
        except Exception as e:
            return {'ok': False, 'error': str(e)}, 500

    @app.route('/users')
    def users():
        """Users management page (shows all customers by default)."""
        search = request.args.get('search', '').strip()
        page = request.args.get('page', 1, type=int)
        per_page_param = (request.args.get('per_page') or '').lower()
        status = (request.args.get('status') or '').strip().lower()
        allowed_page_sizes = {'5': 5, '25': 25, '50': 50, '100': 100}
        if per_page_param == 'all':
            per_page = 10**9  # effectively all on one page
        elif per_page_param in allowed_page_sizes:
            per_page = allowed_page_sizes[per_page_param]
        else:
            per_page = 10

        # Show all customers by default; allow optional status filtering
        query = Customer.query
        if status == 'active':
            query = query.filter_by(is_active=True)
        elif status == 'inactive':
            query = query.filter_by(is_active=False)

        if search:
            search_filter = f"%{search}%"
            query = query.filter(
                db.or_(
                    Customer.first_name.ilike(search_filter),
                    Customer.last_name.ilike(search_filter),
                    Customer.email.ilike(search_filter),
                    Customer.phone.ilike(search_filter),
                    Customer.city.ilike(search_filter)
                )
            )

        customers = query.order_by(Customer.created_at.desc()).paginate(
            page=page, per_page=per_page, error_out=False
        )

        return render_template('users.html', customers=customers, search=search, per_page=per_page_param or str(per_page), status=status)

    @app.route('/shipments')
    def shipments():
        """Shipments management page"""
        search = (request.args.get('search') or '').strip()
        status = (request.args.get('status') or '').strip()
        page = request.args.get('page', 1, type=int)
        per_page_param = request.args.get('per_page', '10')

        query = Shipment.query
        if search:
            s = f"%{search}%"
            query = query.filter(
                db.or_(
                    Shipment.awb.ilike(s),
                    Shipment.sender_name.ilike(s),
                    Shipment.receiver_name.ilike(s),
                    Shipment.origin.ilike(s),
                    Shipment.destination.ilike(s)
                )
            )
        if status:
            query = query.filter(Shipment.status == status)

        # Determine per_page (support 'all')
        if str(per_page_param).lower() == 'all':
            total_count = query.count()
            per_page = max(total_count, 1)
        else:
            try:
                per_page = int(per_page_param)
            except (TypeError, ValueError):
                per_page = 10

        shipments = query.order_by(Shipment.created_at.desc()).paginate(page=page, per_page=per_page, error_out=False)
        statuses = ['Booked', 'In Transit', 'Delivered', 'Cancelled']
        return render_template('shipments.html', shipments=shipments, search=search, status=status, statuses=statuses)

    # Bookings page (admin view similar to Users/Shipments)
    @app.route('/bookings')
    def bookings():
        """Bookings management page showing recently booked shipments"""
        search = (request.args.get('search') or '').strip()
        page = request.args.get('page', 1, type=int)
        per_page_param = request.args.get('per_page', '10')

        query = Shipment.query
        if search:
            s = f"%{search}%"
            query = query.filter(
                db.or_(
                    Shipment.awb.ilike(s),
                    Shipment.sender_name.ilike(s),
                    Shipment.receiver_name.ilike(s),
                    Shipment.origin.ilike(s),
                    Shipment.destination.ilike(s)
                )
            )

        # Per-page handling
        if str(per_page_param).lower() == 'all':
            total_count = query.count()
            per_page = max(total_count, 1)
        else:
            try:
                per_page = int(per_page_param)
            except (TypeError, ValueError):
                per_page = 10

        shipments = query.order_by(Shipment.created_at.desc()).paginate(page=page, per_page=per_page, error_out=False)
        return render_template('bookings.html', shipments=shipments, search=search, per_page=per_page_param)

    # --- Home content management APIs ---
    @app.route('/api/home/services', methods=['GET', 'POST'])
    def api_home_services():
        if request.method == 'GET':
            items = HomeService.query.order_by(HomeService.display_order.asc(), HomeService.id.asc()).all()
            return {'items': [s.to_dict() for s in items]}
        data = request.get_json(silent=True) or {}
        try:
            s = HomeService(
                title=(data.get('title') or '').strip(),
                description=(data.get('description') or '').strip(),
                icon=(data.get('icon') or '').strip() or None,
                image_url=(data.get('image_url') or '').strip() or None,
                display_order=data.get('display_order') or 0,
                active=bool(data.get('active', True))
            )
            if not s.title or not s.description:
                return {'success': False, 'error': 'title and description are required'}, 400
            db.session.add(s)
            db.session.commit()
            return {'success': True, 'item': s.to_dict()}
        except SQLAlchemyError as e:
            db.session.rollback()
            return {'success': False, 'error': str(e)}, 500

    @app.route('/api/home/services/<int:item_id>', methods=['PUT', 'DELETE'])
    def api_home_service_detail(item_id):
        s = db.session.get(HomeService, item_id)
        if not s:
            return {'success': False, 'error': 'Not found'}, 404
        if request.method == 'DELETE':
            try:
                db.session.delete(s)
                db.session.commit()
                return {'success': True}
            except SQLAlchemyError as e:
                db.session.rollback()
                return {'success': False, 'error': str(e)}, 500
        # PUT
        data = request.get_json(silent=True) or {}
        try:
            if 'title' in data: s.title = (data.get('title') or '').strip()
            if 'description' in data: s.description = (data.get('description') or '').strip()
            if 'icon' in data: s.icon = (data.get('icon') or '').strip() or None
            if 'image_url' in data: s.image_url = (data.get('image_url') or '').strip() or None
            if 'display_order' in data: s.display_order = int(data.get('display_order') or 0)
            if 'active' in data: s.active = bool(data.get('active'))
            db.session.commit()
            return {'success': True, 'item': s.to_dict()}
        except SQLAlchemyError as e:
            db.session.rollback()
            return {'success': False, 'error': str(e)}, 500

    @app.route('/api/home/features', methods=['GET', 'POST'])
    def api_home_features():
        if request.method == 'GET':
            items = HomeFeature.query.order_by(HomeFeature.display_order.asc(), HomeFeature.id.asc()).all()
            return {'items': [f.to_dict() for f in items]}
        data = request.get_json(silent=True) or {}
        try:
            f = HomeFeature(
                title=(data.get('title') or '').strip(),
                description=(data.get('description') or '').strip(),
                icon=(data.get('icon') or '').strip() or None,
                display_order=data.get('display_order') or 0,
                active=bool(data.get('active', True))
            )
            if not f.title or not f.description:
                return {'success': False, 'error': 'title and description are required'}, 400
            db.session.add(f)
            db.session.commit()
            return {'success': True, 'item': f.to_dict()}
        except SQLAlchemyError as e:
            db.session.rollback()
            return {'success': False, 'error': str(e)}, 500

    @app.route('/api/home/features/<int:item_id>', methods=['PUT', 'DELETE'])
    def api_home_feature_detail(item_id):
        f = db.session.get(HomeFeature, item_id)
        if not f:
            return {'success': False, 'error': 'Not found'}, 404
        if request.method == 'DELETE':
            try:
                db.session.delete(f)
                db.session.commit()
                return {'success': True}
            except SQLAlchemyError as e:
                db.session.rollback()
                return {'success': False, 'error': str(e)}, 500
        # PUT
        data = request.get_json(silent=True) or {}
        try:
            if 'title' in data: f.title = (data.get('title') or '').strip()
            if 'description' in data: f.description = (data.get('description') or '').strip()
            if 'icon' in data: f.icon = (data.get('icon') or '').strip() or None
            if 'display_order' in data: f.display_order = int(data.get('display_order') or 0)
            if 'active' in data: f.active = bool(data.get('active'))
            db.session.commit()
            return {'success': True, 'item': f.to_dict()}
        except SQLAlchemyError as e:
            db.session.rollback()
            return {'success': False, 'error': str(e)}, 500

    @app.route('/api/customers/<int:customer_id>', methods=['GET'])
    def get_customer_details(customer_id):
        """API endpoint to get customer details"""
        customer = db.session.get(Customer, customer_id)
        if not customer:
            return {'error': 'Customer not found'}, 404
        
        return {
            'id': customer.id,
            'first_name': customer.first_name,
            'last_name': customer.last_name,
            'full_name': customer.full_name,
            'email': customer.email,
            'phone': customer.phone,
            'address': customer.address,
            'city': customer.city,
            'state': customer.state,
            'postal_code': customer.postal_code,
            'country': customer.country,
            'full_address': customer.full_address,
            'created_at': customer.created_at.strftime('%Y-%m-%d %H:%M:%S'),
            'is_active': customer.is_active,
            'created_date': customer.created_at.strftime('%d %b %Y'),
            'created_time': customer.created_at.strftime('%H:%M')
        }
        
    @app.route('/api/customers/<int:customer_id>/approve', methods=['POST'])
    def approve_customer(customer_id):
        """API endpoint to approve a customer"""
        try:
            customer = db.session.get(Customer, customer_id)
            if not customer:
                return {'success': False, 'error': 'Customer not found'}, 404
                
            if customer.is_active:
                return {'success': True, 'message': 'Customer is already approved'}
                
            customer.is_active = True
            db.session.commit()

            # Create notification for customer approval
            create_notification(
                'message',
                'Customer Approved',
                f'Customer {customer.full_name} has been approved',
                customer.id
            )

            # Send confirmation email to customer (non-blocking best-effort)
            if customer.email:
                subject = 'Your account has been approved'
                body = (
                    f'Hi {customer.full_name},\n\n'
                    'Your account has been approved by the admin. You can now access all features.\n\n'
                    'Thank you,\nAdmin Team'
                )
                send_email(customer.email, subject, body)

            return {'success': True, 'message': 'Customer approved successfully'}
            
        except SQLAlchemyError as e:
            db.session.rollback()
            return {'success': False, 'error': str(e)}, 500

    @app.route('/api/customers', methods=['POST'])
    def create_customer():
        """Create a new customer record in the customers table.
        Accepts JSON or form-encoded data.
        Required: first_name, last_name, email
        Optional: phone, address, city, state, postal_code, country, is_active
        """
        data = request.get_json(silent=True) or request.form.to_dict() or {}

        first_name = (data.get('first_name') or '').strip()
        last_name = (data.get('last_name') or '').strip()
        email = (data.get('email') or '').strip().lower()
        phone = (data.get('phone') or '').strip() or None
        address = (data.get('address') or '').strip() or None
        city = (data.get('city') or '').strip() or None
        state = (data.get('state') or '').strip() or None
        postal_code = (data.get('postal_code') or '').strip() or None
        country = (data.get('country') or '').strip() or None
        raw_active = data.get('is_active')
        # Default to True when created by admin
        if raw_active is None:
            is_active = True
        elif isinstance(raw_active, bool):
            is_active = raw_active
        elif isinstance(raw_active, (int, float)):
            is_active = int(raw_active) != 0
        elif isinstance(raw_active, str):
            v = raw_active.strip().lower()
            is_active = v in ('true', '1', 'yes', 'y')
        else:
            is_active = True

        # Basic validation
        missing = [k for k, v in {
            'first_name': first_name,
            'last_name': last_name,
            'email': email
        }.items() if not v]
        if missing:
            return {'success': False, 'error': f"Missing required fields: {', '.join(missing)}"}, 400

        # Duplicate email check
        if Customer.query.filter_by(email=email).first():
            return {'success': False, 'error': 'Email already exists'}, 409

        try:
            customer = Customer(
                first_name=first_name,
                last_name=last_name,
                email=email,
                phone=phone,
                address=address,
                city=city,
                state=state,
                postal_code=postal_code,
                country=country,
                is_active=is_active
            )
            db.session.add(customer)
            db.session.commit()

            # Notify admin area about creation
            create_notification(
                'message',
                'Customer Created (Admin)',
                f'New customer {customer.full_name} added with email: {customer.email}',
                customer.id
            )

            return {
                'success': True,
                'customer': {
                    'id': customer.id,
                    'first_name': customer.first_name,
                    'last_name': customer.last_name,
                    'full_name': customer.full_name,
                    'email': customer.email,
                    'phone': customer.phone,
                    'city': customer.city,
                    'country': customer.country,
                    'is_active': customer.is_active,
                    'created_at': customer.created_at.strftime('%Y-%m-%d %H:%M:%S'),
                }
            }, 201
        except SQLAlchemyError as e:
            db.session.rollback()
            return {'success': False, 'error': str(e)}, 500

    @app.route('/track')
    def track():
        awb = request.args.get('awb', '').strip()
        shipment = None
        events = []
        if awb:
            shipment = Shipment.query.filter_by(awb=awb).first()
            if shipment:
                events = shipment.events.order_by(TrackingEvent.created_at.desc()).all()
            else:
                flash('Shipment not found for given AWB', 'error')
        return render_template('track.html', shipment=shipment, events=events, awb=awb)

    @app.route('/shipments/<int:shipment_id>/events', methods=['POST'])
    def add_event(shipment_id):
        shipment = db.session.get(Shipment, shipment_id)
        if not shipment:
            flash('Shipment not found', 'error')
            return redirect(url_for('index'))
        status = request.form.get('status', '').strip()
        location = request.form.get('location', '').strip() or None
        note = request.form.get('note', '').strip() or None
        if not status:
            flash('Status is required', 'error')
            return redirect(url_for('index'))
        try:
            event = TrackingEvent(shipment_id=shipment.id, status=status, location=location, note=note)
            shipment.status = status  # keep current status in shipment
            db.session.add(event)
            db.session.commit()
            
            # Create notification for shipment update
            create_notification(
                'shipment',
                'Shipment Status Updated',
                f'Shipment {shipment.awb} status changed to: {status}',
                shipment.id
            )
            
            flash('Tracking event added', 'success')
        except SQLAlchemyError as e:
            db.session.rollback()
            flash(f'Error adding event: {str(e)}', 'error')
        return redirect(url_for('track', awb=shipment.awb))

    return app


if __name__ == '__main__':
    app = create_app()
    host = os.getenv('FLASK_RUN_HOST', '127.0.0.1')
    port = int(os.getenv('FLASK_RUN_PORT', '5000'))
    app.run(host=host, port=port, debug=True)

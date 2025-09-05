from flask import Flask, render_template, request, redirect, url_for, flash
from flask_sqlalchemy import SQLAlchemy
from sqlalchemy.exc import SQLAlchemyError
from dotenv import load_dotenv
import os
import datetime
import random
import string

# Global SQLAlchemy instance

db = SQLAlchemy()


def create_app():
    """Application factory for the Flask app."""
    # Load .env and allow overriding existing environment variables
    load_dotenv(override=True)

    app = Flask(__name__, static_folder='static', template_folder='templates')
    app.config.from_object('config.Config')

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

    # Create tables if they don't exist
    with app.app_context():
        db.create_all()
        
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
        return render_template('dashboard.html')

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
            'updated_at': admin.updated_at.strftime('%Y-%m-%d %H:%M:%S') if admin.updated_at else ''
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
            'updated_at': admin.updated_at.strftime('%Y-%m-%d %H:%M:%S') if admin.updated_at else ''
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

        try:
            admin = get_or_create_admin()
            admin.name = name
            admin.email = email or None
            admin.phone = phone or None
            admin.company = company or None
            admin.address = address or None
            db.session.commit()

            return {
                'success': True,
                'admin': {
                    'name': admin.name or '',
                    'email': admin.email or '',
                    'phone': admin.phone or '',
                    'company': admin.company or '',
                    'address': admin.address or '',
                    'updated_at': admin.updated_at.strftime('%Y-%m-%d %H:%M:%S') if admin.updated_at else ''
                }
            }
        except SQLAlchemyError as e:
            db.session.rollback()
            return {'success': False, 'error': str(e)}, 500

    @app.route('/notifications')
    def notifications_page():
        """Full notifications page showing read and unread notifications"""
        notifications = get_all_notifications(limit=None)
        return render_template('notifications.html', notifications=notifications)

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
            cutoff = datetime.datetime.utcnow() - datetime.timedelta(hours=max_age_hours)
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
        """Users management page"""
        search = request.args.get('search', '').strip()
        page = request.args.get('page', 1, type=int)
        per_page = 10
        
        # Only show approved (active) customers
        query = Customer.query.filter_by(is_active=True)
        
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
        
        return render_template('users.html', customers=customers, search=search)

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

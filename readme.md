# TekiPlanet - Comprehensive Digital Platform

## 📋 Table of Contents
- [Overview](#overview)
- [Architecture](#architecture)
- [Features](#features)
- [Technology Stack](#technology-stack)
- [Installation & Setup](#installation--setup)
- [API Documentation](#api-documentation)
- [Mobile App](#mobile-app)
- [Security Features](#security-features)
- [Business Model](#business-model)
- [Development Guidelines](#development-guidelines)
- [Deployment](#deployment)
- [Contributing](#contributing)

## 🚀 Overview

**TekiPlanet** is a comprehensive digital platform that combines multiple business verticals into a unified ecosystem. It serves as a multi-sided marketplace connecting students, professionals, businesses, and service providers through an integrated platform offering e-learning, e-commerce, professional services, project management, and business solutions.

### Core Value Proposition
- **Unified Platform**: Single ecosystem for education, commerce, and professional services
- **Multi-Role Support**: Students, Professionals, Businesses, and Administrators
- **Mobile-First**: Native mobile app with web interface
- **Real-Time Communication**: Live notifications and messaging
- **Secure Payments**: Integrated payment processing with escrow services

## 🏗️ Architecture

### Backend Architecture (Laravel 10)
```
backend/
├── app/
│   ├── Models/           # Eloquent models (100+ models)
│   ├── Http/Controllers/ # API controllers
│   ├── Services/         # Business logic services
│   ├── Events/          # Event handling
│   ├── Jobs/            # Background jobs
│   ├── Notifications/   # Notification classes
│   ├── Mail/            # Email templates
│   ├── Helpers/         # Utility functions
│   └── Enums/           # Enumeration classes
├── routes/
│   ├── api.php          # API routes (541 lines)
│   ├── admin.php        # Admin panel routes
│   ├── web.php          # Web routes
│   └── channels.php     # Broadcasting channels
├── database/
│   ├── migrations/      # Database migrations
│   ├── seeders/         # Data seeders
│   └── factories/       # Model factories
└── config/              # Configuration files
```

### Frontend Architecture (React + TypeScript)
```
frontend/
├── src/
│   ├── components/      # Reusable UI components
│   ├── pages/          # Page components (40+ pages)
│   ├── hooks/          # Custom React hooks
│   ├── store/          # Zustand state management
│   ├── services/       # API service layer
│   ├── types/          # TypeScript type definitions
│   ├── utils/          # Utility functions
│   ├── contexts/       # React contexts
│   └── styles/         # Global styles
├── public/             # Static assets
└── config/             # Build configuration
```

## ✨ Features

### 🔐 Authentication & User Management
- **Multi-Role System**: Students, Businesses, Professionals, Administrators
- **Email Verification**: Secure email verification with resend functionality
- **Two-Factor Authentication**: Google2FA integration with recovery codes
- **Password Reset**: Email-based password reset with verification codes
- **Profile Management**: Avatar uploads, preferences, and settings
- **User Preferences**: Dark mode, notification settings, language, timezone
- **Account Types**: Dynamic user type switching (student, business, professional)

### 💰 Digital Wallet & Payments
- **Wallet System**: User wallet with balance tracking
- **Bank Transfer**: Direct bank transfer integration
- **Paystack Integration**: Secure payment processing
- **Transaction History**: Comprehensive transaction logging with filtering
- **Receipt Generation**: PDF receipt generation for transactions
- **Withdrawal System**: Bank account verification and withdrawal processing
- **Payment Callbacks**: Secure payment verification and confirmation

### 🎓 E-Learning Academy
- **Course Management**: Comprehensive course creation and management
- **Course Categories**: Organized course categorization and levels
- **Enrollment System**: Student enrollment with installment payment options
- **Curriculum Management**: Modules, lessons, and topics organization
- **Exams & Assessments**: Interactive exams with participation tracking
- **Course Notices**: Announcements and notifications for enrolled students
- **Certificate Generation**: Automated certificate creation and management
- **Course Reviews**: Student reviews and rating system
- **Progress Tracking**: Learning progress monitoring
- **Course Features**: Detailed course features and benefits
- **Instructor Management**: Instructor profiles and course assignments

### 🛒 E-Commerce Store
- **Product Catalog**: Comprehensive product management with categories
- **Brand Management**: Product brand organization
- **Shopping Cart**: Full cart functionality with quantity management
- **Wishlist System**: User wishlist with toggle functionality
- **Order Processing**: Complete order lifecycle management
- **Order Tracking**: Real-time order status and tracking
- **Product Reviews**: Customer review and rating system
- **Promotions**: Coupon system and promotional offers
- **Shipping Management**: Multiple shipping methods and zones
- **Product Requests**: Customer product request system
- **Inventory Management**: Stock tracking and management

### 👨‍💼 Professional Services
- **Service Categories**: Organized service categorization
- **Quote Request System**: Custom quote request forms with dynamic fields
- **Consulting Bookings**: Professional consulting appointment system
- **Professional Profiles**: Detailed professional profiles with categories
- **Service Reviews**: Client review and rating system
- **Project Management**: Service delivery project tracking
- **Time Slot Management**: Available time slot booking system

### 🏢 Business Management
- **Business Profiles**: Complete business profile management
- **Customer Management**: Comprehensive customer relationship management
- **Invoice Generation**: Automated invoice creation and management
- **Financial Tracking**: Business metrics and financial reporting
- **Business Activities**: Activity monitoring and logging
- **Inventory Management**: Product and service inventory tracking
- **Business Dashboard**: Comprehensive business analytics

### 📋 Project Management
- **Project Creation**: Complete project setup and management
- **Team Assignment**: Professional team member assignment
- **Project Stages**: Milestone and stage tracking
- **File Management**: Project document and file organization
- **Project Invoices**: Project-specific invoicing
- **Client Communication**: Integrated client messaging system
- **Progress Tracking**: Real-time project progress monitoring

### 💼 Hustle/Gig Marketplace
- **Job Posting**: Comprehensive job posting system
- **Application Management**: Professional application handling
- **Professional Assignment**: Automatic or manual professional assignment
- **Payment Escrow**: Secure payment escrow system
- **Messaging System**: Integrated communication between parties
- **Progress Tracking**: Job completion progress monitoring
- **Payment Release**: Milestone-based payment release

### 🏢 Workstation Management
- **Subscription Plans**: Flexible workspace subscription options
- **Access Cards**: Digital access card generation
- **Payment Processing**: Subscription payment handling
- **Subscription History**: Complete subscription management
- **Access Control**: Workspace access management

### 💻 IT Consulting
- **Consulting Services**: Specialized IT consulting booking
- **Time Slot Management**: Available consulting time management
- **Booking Management**: Appointment booking with cancellation
- **Consulting Reviews**: Client feedback and review system

### 🔔 Notifications & Communication
- **Real-Time Notifications**: Pusher-powered live notifications
- **Push Notifications**: Mobile push notification system
- **Email Notifications**: Comprehensive email notification system
- **In-App Messaging**: Integrated messaging system
- **Device Token Management**: Mobile device token handling
- **Notification Preferences**: User notification customization

### 👨‍💻 Admin Panel
- **User Management**: Comprehensive user administration
- **Course Administration**: Course creation and management
- **Transaction Monitoring**: Financial transaction oversight
- **System Settings**: Platform configuration management
- **Analytics & Reporting**: Business intelligence and reporting

## 🛠️ Technology Stack

### Backend Technologies
- **Framework**: Laravel 10 (PHP 8.1+)
- **Authentication**: Laravel Sanctum (JWT-based)
- **Database**: MySQL with Eloquent ORM
- **Payment Processing**: Paystack PHP SDK
- **Real-Time**: Pusher for live notifications
- **Security**: Google2FA for two-factor authentication
- **File Processing**: Intervention Image for image manipulation
- **PDF Generation**: TCPDF and DomPDF
- **QR Code**: Endroid QR Code and Simple QR Code
- **Push Notifications**: Firebase PHP SDK
- **Email**: Laravel Mail with SMTP
- **Caching**: Redis (optional)
- **Queue**: Laravel Queue for background jobs

### Frontend Technologies
- **Framework**: React 18 with TypeScript
- **Build Tool**: Vite for fast development
- **Styling**: Tailwind CSS with shadcn/ui components
- **State Management**: Zustand + React Query (TanStack Query)
- **Routing**: React Router DOM v6
- **Mobile**: Capacitor for native mobile capabilities
- **UI Components**: Radix UI primitives
- **Charts**: Recharts for data visualization
- **Forms**: React Hook Form with Zod validation
- **Notifications**: React Hot Toast and Sonner
- **Icons**: Lucide React
- **Date Handling**: date-fns
- **Internationalization**: React i18n (planned)

### Mobile App (Capacitor)
- **Platform**: iOS and Android
- **Native Features**: Push notifications, file system, camera
- **Deep Linking**: Payment callback handling
- **Splash Screen**: Custom splash screen configuration
- **Status Bar**: Native status bar management
- **Keyboard**: Native keyboard handling
- **Haptics**: Device haptic feedback
- **Local Notifications**: In-app notification system

### Development Tools
- **Version Control**: Git
- **Package Management**: Composer (PHP), npm (Node.js)
- **Code Quality**: ESLint, PHP CS Fixer
- **Testing**: PHPUnit, Jest (planned)
- **API Documentation**: OpenAPI/Swagger (planned)

## 📦 Installation & Setup

### Prerequisites
- PHP 8.1 or higher
- Composer
- Node.js 18+ and npm
- MySQL 8.0+
- Redis (optional, for caching)
- XAMPP/WAMP/MAMP (for local development)

### Backend Setup

1. **Clone the repository**
```bash
git clone <repository-url>
cd tekiplanet/backend
```

2. **Install PHP dependencies**
```bash
composer install
```

3. **Environment configuration**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Configure database in `.env`**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tekiplanet
DB_USERNAME=root
DB_PASSWORD=
```

5. **Run database migrations**
```bash
php artisan migrate
php artisan db:seed
```

6. **Configure storage**
```bash
php artisan storage:link
```

7. **Configure services in `.env`**
```env
# Paystack Configuration
PAYSTACK_SECRET_KEY=your_paystack_secret_key
PAYSTACK_PUBLIC_KEY=your_paystack_public_key

# Pusher Configuration
PUSHER_APP_ID=your_pusher_app_id
PUSHER_APP_KEY=your_pusher_key
PUSHER_APP_SECRET=your_pusher_secret
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=your_cluster

# Firebase Configuration
FIREBASE_CREDENTIALS=path_to_firebase_credentials.json
```

8. **Start the development server**
```bash
php artisan serve
```

### Frontend Setup

1. **Navigate to frontend directory**
```bash
cd ../frontend
```

2. **Install Node.js dependencies**
```bash
npm install
```

3. **Configure environment variables**
```bash
cp .env.example .env
```

4. **Update API base URL in `.env`**
```env
VITE_API_BASE_URL=http://localhost:8000/api
VITE_PUSHER_APP_KEY=your_pusher_key
VITE_PUSHER_APP_CLUSTER=your_cluster
```

5. **Start development server**
```bash
npm run dev
```

### Mobile App Setup

1. **Install Capacitor CLI**
```bash
npm install -g @capacitor/cli
```

2. **Build the web app**
```bash
npm run build
```

3. **Add mobile platforms**
```bash
npx cap add android
npx cap add ios
```

4. **Sync web code to native projects**
```bash
npx cap sync
```

5. **Open in native IDEs**
```bash
npx cap open android  # Opens Android Studio
npx cap open ios      # Opens Xcode (macOS only)
```

## 📚 API Documentation

### Authentication Endpoints
```
POST /api/register              # User registration
POST /api/login                 # User login
POST /api/logout                # User logout
POST /api/email/verify          # Email verification
POST /api/email/resend          # Resend verification email
GET  /api/user                  # Get current user
```

### Wallet & Payments
```
POST /api/wallet/bank-transfer              # Bank transfer payment
POST /api/wallet/initiate-paystack-payment  # Initiate Paystack payment
POST /api/wallet/verify-paystack-payment    # Verify Paystack payment
GET  /api/user/wallet-balance               # Get wallet balance
GET  /api/transactions                      # Get transaction history
GET  /api/transactions/{id}                 # Get transaction details
```

### E-Learning
```
GET  /api/courses                           # Get all courses
GET  /api/courses/{id}                      # Get course details
POST /api/enrollments/enroll                # Enroll in course
GET  /api/enrollments                       # Get user enrollments
GET  /api/courses/{id}/curriculum           # Get course curriculum
GET  /api/courses/{id}/exams                # Get course exams
```

### E-Commerce
```
GET  /api/products                          # Get products
GET  /api/products/{id}                     # Get product details
POST /api/cart/add                          # Add to cart
GET  /api/cart                              # Get cart
POST /api/orders                            # Create order
GET  /api/orders                            # Get orders
GET  /api/orders/{id}/tracking              # Order tracking
```

### Professional Services
```
GET  /api/services/categories               # Get service categories
POST /api/quotes                            # Create quote request
GET  /api/quotes                            # Get user quotes
GET  /api/quotes/{id}                       # Get quote details
POST /api/consulting/bookings               # Create consulting booking
GET  /api/consulting/bookings               # Get consulting bookings
```

### Business Management
```
GET  /api/business/profile                  # Get business profile
POST /api/business/profile                  # Create business profile
PUT  /api/business/profile                  # Update business profile
GET  /api/business/customers                # Get business customers
POST /api/business/invoices                 # Create invoice
GET  /api/business/metrics                  # Get business metrics
```

### Project Management
```
GET  /api/projects                          # Get user projects
GET  /api/projects/{id}                     # Get project details
POST /api/projects                          # Create project
PUT  /api/projects/{id}                     # Update project
PUT  /api/projects/{id}/progress            # Update project progress
```

### Hustle Marketplace
```
GET  /api/hustles                           # Get available hustles
GET  /api/hustles/{id}                      # Get hustle details
POST /api/hustles/{id}/apply                # Apply for hustle
GET  /api/hustle-applications               # Get user applications
GET  /api/my-hustles                        # Get assigned hustles
```

### Workstation Management
```
GET  /api/workstation/plans                 # Get workstation plans
POST /api/workstation/subscriptions          # Create subscription
GET  /api/workstation/subscription           # Get current subscription
POST /api/workstation/subscriptions/{id}/renew    # Renew subscription
```

### Notifications
```
GET  /api/notifications                     # Get user notifications
POST /api/notifications/{id}/read           # Mark notification as read
POST /api/notifications/mark-all-read       # Mark all as read
DELETE /api/notifications/{id}              # Delete notification
```

### Two-Factor Authentication
```
POST /api/auth/2fa/enable                   # Enable 2FA
POST /api/auth/2fa/verify-setup             # Verify 2FA setup
POST /api/auth/2fa/verify                   # Verify 2FA code
POST /api/auth/2fa/disable                  # Disable 2FA
GET  /api/auth/2fa/recovery-codes           # Get recovery codes
```

## 📱 Mobile App

### Features
- **Native Performance**: Capacitor-powered native mobile experience
- **Push Notifications**: Firebase-powered push notifications
- **Offline Support**: Basic offline functionality
- **Deep Linking**: Payment callback handling
- **File System Access**: Download and file management
- **Camera Integration**: Photo capture and upload
- **Haptic Feedback**: Device vibration feedback
- **Splash Screen**: Custom app launch experience

### Platform Support
- **Android**: API level 21+ (Android 5.0+)
- **iOS**: iOS 12.0+
- **Web**: Progressive Web App (PWA) capabilities

### Build Commands
```bash
# Build for web
npm run build

# Build for Android
npm run build
npx cap sync android
npx cap open android

# Build for iOS
npm run build
npx cap sync ios
npx cap open ios
```

## 🔒 Security Features

### Authentication Security
- **JWT Tokens**: Secure token-based authentication
- **Two-Factor Authentication**: Google2FA integration
- **Email Verification**: Mandatory email verification
- **Password Policies**: Strong password requirements
- **Session Management**: Secure session handling
- **Rate Limiting**: API rate limiting protection

### Data Security
- **Input Validation**: Comprehensive input sanitization
- **SQL Injection Protection**: Eloquent ORM protection
- **XSS Protection**: Cross-site scripting prevention
- **CSRF Protection**: Cross-site request forgery protection
- **File Upload Security**: Secure file upload handling
- **Data Encryption**: Sensitive data encryption

### Payment Security
- **PCI Compliance**: Payment card industry compliance
- **Secure Payment Processing**: Paystack integration
- **Transaction Verification**: Payment verification system
- **Escrow Services**: Secure payment escrow
- **Receipt Generation**: Secure transaction receipts

### API Security
- **CORS Configuration**: Cross-origin resource sharing
- **API Rate Limiting**: Request rate limiting
- **Authentication Middleware**: Protected route middleware
- **Role-Based Access**: Role-based authorization
- **API Versioning**: API version management

## 💼 Business Model

### Revenue Streams
1. **Course Commissions**: Percentage from course sales
2. **Service Fees**: Commission from professional services
3. **Subscription Fees**: Workstation subscription revenue
4. **Transaction Fees**: Payment processing fees
5. **Premium Features**: Advanced features for businesses
6. **Advertising**: Platform advertising revenue

### User Segments
- **Students**: E-learning course participants
- **Professionals**: Service providers and freelancers
- **Businesses**: Companies using business management tools
- **Instructors**: Course creators and educators
- **Administrators**: Platform management team

### Value Propositions
- **For Students**: Quality education with flexible payment options
- **For Professionals**: Platform to showcase skills and earn income
- **For Businesses**: Comprehensive business management solution
- **For Instructors**: Easy course creation and monetization platform

## 🚀 Development Guidelines

### Code Standards
- **PHP**: PSR-12 coding standards
- **JavaScript**: ESLint configuration
- **TypeScript**: Strict type checking
- **React**: Functional components with hooks
- **Laravel**: Laravel best practices

### Git Workflow
```bash
# Feature development
git checkout -b feature/feature-name
git add .
git commit -m "feat: add feature description"
git push origin feature/feature-name

# Bug fixes
git checkout -b fix/bug-description
git add .
git commit -m "fix: bug description"
git push origin fix/bug-description
```

### Testing Strategy
- **Unit Tests**: PHPUnit for backend testing
- **Integration Tests**: API endpoint testing
- **Frontend Tests**: Jest and React Testing Library (planned)
- **E2E Tests**: Cypress or Playwright (planned)

### Performance Optimization
- **Database**: Query optimization and indexing
- **Caching**: Redis caching implementation
- **Frontend**: Code splitting and lazy loading
- **Images**: Image optimization and compression
- **CDN**: Content delivery network integration

## 🚀 Deployment

### Backend Deployment
1. **Server Requirements**
   - PHP 8.1+
   - MySQL 8.0+
   - Redis (optional)
   - SSL certificate
   - Domain name

2. **Deployment Steps**
```bash
# Clone repository
git clone <repository-url>
cd backend

# Install dependencies
composer install --optimize-autoloader --no-dev

# Environment setup
cp .env.example .env
# Configure production environment variables

# Database setup
php artisan migrate --force
php artisan storage:link

# Cache optimization
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Queue worker setup
php artisan queue:work --daemon
```

### Frontend Deployment
1. **Build for production**
```bash
npm run build
```

2. **Deploy to hosting service**
   - Netlify
   - Vercel
   - AWS S3
   - DigitalOcean App Platform

### Mobile App Deployment
1. **Android**
   - Build APK/AAB in Android Studio
   - Upload to Google Play Console
   - Configure app signing

2. **iOS**
   - Build in Xcode
   - Upload to App Store Connect
   - Configure app signing and provisioning

## 🤝 Contributing

### Development Setup
1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests (if applicable)
5. Submit a pull request

### Code Review Process
1. **Automated Checks**: CI/CD pipeline validation
2. **Code Review**: Peer review process
3. **Testing**: Automated and manual testing
4. **Documentation**: Update relevant documentation

### Issue Reporting
- Use GitHub Issues for bug reports
- Provide detailed reproduction steps
- Include environment information
- Add screenshots/videos when relevant

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 📞 Support

- **Documentation**: [Project Wiki](link-to-wiki)
- **Issues**: [GitHub Issues](link-to-issues)
- **Discussions**: [GitHub Discussions](link-to-discussions)
- **Email**: support@tekiplanet.org

## 🔄 Version History

### v1.0.0 (Current)
- Initial release with core features
- Multi-role user system
- E-learning platform
- E-commerce functionality
- Professional services marketplace
- Business management tools
- Mobile app support

### Planned Features
- **v1.1.0**: Advanced analytics and reporting
- **v1.2.0**: AI-powered recommendations
- **v1.3.0**: Advanced mobile features
- **v2.0.0**: Multi-language support and internationalization

---

**TekiPlanet** - Empowering digital transformation through unified learning, commerce, and professional services.

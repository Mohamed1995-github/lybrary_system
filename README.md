# 📚 Modern Library Management System

A beautiful, modern, and responsive library management system built with PHP, featuring a bilingual interface (Arabic/French) and a contemporary design system.

## ✨ Features

### 🎨 Modern Design
- **Contemporary UI/UX**: Clean, modern interface with smooth animations
- **Responsive Design**: Works perfectly on desktop, tablet, and mobile devices
- **Dark Mode Support**: Automatic dark mode detection and manual toggle
- **Bilingual Interface**: Full support for Arabic (RTL) and French (LTR) languages
- **Interactive Elements**: Hover effects, transitions, and micro-interactions

### 📖 Library Management
- **Item Management**: Add, edit, and delete books, magazines, newspapers, and general materials
- **Multi-language Support**: Separate catalogs for Arabic and French materials
- **Inventory Tracking**: Track total copies and available copies
- **Status Indicators**: Visual status badges showing availability
- **Search & Filter**: Real-time search functionality

### 🔐 Security & Authentication
- **User Authentication**: Secure login system
- **Session Management**: Proper session handling
- **Input Validation**: Client-side and server-side validation
- **SQL Injection Protection**: Prepared statements

### 🛠 Technical Features
- **Modern CSS**: CSS Grid, Flexbox, and CSS Custom Properties
- **JavaScript Enhancements**: Interactive features and form validation
- **Font Awesome Icons**: Beautiful iconography throughout the interface
- **Google Fonts**: Inter font family for excellent typography
- **Progressive Enhancement**: Works without JavaScript, enhanced with it

## 🚀 Quick Start

### Prerequisites
- PHP 7.4 or higher
- MySQL/MariaDB database
- Web server (Apache/Nginx)

### Installation

1. **Clone or download the project**
   ```bash
   git clone [repository-url]
   cd library_system
   ```

2. **Set up the database**
   - Create a MySQL database
   - Import the database schema (see `config/db.php` for connection details)

3. **Configure the database connection**
   - Edit `config/db.php` with your database credentials

4. **Set up the web server**
   - Point your web server to the project directory
   - Ensure PHP has write permissions

5. **Access the system**
   - Navigate to `public/login.php`
   - Use the default credentials (check the database for initial user)

## 📁 Project Structure

```
library_system/
├── config/
│   └── db.php                 # Database configuration
├── includes/
│   ├── auth.php              # Authentication functions
│   └── functions.php         # Utility functions
├── lang/
│   ├── ar.php               # Arabic translations
│   └── fr.php               # French translations
├── modules/
│   ├── acquisitions/         # Acquisition management
│   ├── items/               # Item management
│   └── loans/               # Loan management
├── public/
│   ├── assets/
│   │   ├── css/
│   │   │   └── style.css    # Main stylesheet
│   │   └── js/
│   │       └── script.js    # JavaScript enhancements
│   ├── dashboard.php        # Main dashboard
│   ├── login.php           # Login page
│   └── logout.php          # Logout handler
└── README.md
```

## 🎨 Design System

### Color Palette
- **Primary**: `#2563eb` (Blue)
- **Secondary**: `#059669` (Green)
- **Accent**: `#7c3aed` (Purple)
- **Success**: `#10b981` (Green)
- **Warning**: `#f59e0b` (Yellow)
- **Error**: `#ef4444` (Red)

### Typography
- **Font Family**: Inter (Google Fonts)
- **Weights**: 400, 500, 600, 700
- **Responsive**: Scales appropriately on all devices

### Components
- **Cards**: Elevated containers with shadows and hover effects
- **Buttons**: Multiple styles (primary, secondary, outline)
- **Forms**: Modern form inputs with validation
- **Tables**: Responsive tables with hover effects
- **Navigation**: Clean navigation with active states

## 🌐 Language Support

The system supports two languages:

### Arabic (RTL)
- Right-to-left text direction
- Arabic translations for all interface elements
- Culturally appropriate design elements

### French (LTR)
- Left-to-right text direction
- French translations for all interface elements
- Standard Western design patterns

## 📱 Responsive Design

The system is fully responsive and optimized for:

- **Desktop**: Full-featured interface with side-by-side layouts
- **Tablet**: Adapted layouts with touch-friendly elements
- **Mobile**: Single-column layouts with optimized touch targets

## 🔧 Customization

### Adding New Languages
1. Create a new language file in `lang/` directory
2. Add translations for all interface elements
3. Update the language selection logic

### Modifying the Design
1. Edit `public/assets/css/style.css` for global styles
2. Use CSS custom properties for easy theming
3. Add page-specific styles in individual PHP files

### Adding New Features
1. Create new modules in the `modules/` directory
2. Follow the existing code structure and naming conventions
3. Include the main CSS and JS files for consistency

## 🚀 Performance Features

- **Optimized CSS**: Efficient selectors and minimal redundancy
- **Lazy Loading**: Images and heavy content load on demand
- **Minimal JavaScript**: Lightweight enhancements without bloat
- **Fast Rendering**: Optimized HTML structure for quick page loads

## 🔒 Security Considerations

- **SQL Injection Protection**: All database queries use prepared statements
- **XSS Prevention**: Output is properly escaped
- **Session Security**: Secure session handling
- **Input Validation**: Both client-side and server-side validation

## 📊 Browser Support

- **Modern Browsers**: Chrome, Firefox, Safari, Edge (latest versions)
- **Mobile Browsers**: iOS Safari, Chrome Mobile, Samsung Internet
- **Fallbacks**: Graceful degradation for older browsers

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📄 License

This project is open source and available under the [MIT License](LICENSE).

## 🆘 Support

For support and questions:
- Check the documentation
- Review the code comments
- Create an issue on the repository

---

**Built with ❤️ for modern library management**

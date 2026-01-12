# AdminLTE Integration - Quick Reference

## 🚀 Quick Start

### Access New Pages:
- Login: `http://yourserver/src/login_new.php`
- Dashboard: `http://yourserver/src/index_new.php`
- Charts: `http://yourserver/src/charts_new.php`
- Alerts: `http://yourserver/src/alerts_new.php`
- Users: `http://yourserver/src/users_new.php`

## 📦 What's Included

### Phase 1 & 2 (✅ Complete):
- ✅ AdminLTE 3.2 integration
- ✅ Responsive sidebar & navbar
- ✅ Modern login/register pages
- ✅ Dashboard with live stats
- ✅ Interactive charts (Chart.js)
- ✅ DataTables for data display
- ✅ Dark mode toggle
- ✅ Alert management system
- ✅ User management (admin)
- ✅ Real-time data updates
- ✅ Mobile responsive design

### Phase 3 (🔄 Ready):
- Animations & transitions
- Toast notifications
- Loading states
- Mobile optimizations

### Phase 4 (📋 Planned):
- Advanced admin features
- Export functionality
- Audit logs
- Email notifications

## 🎨 Color Scheme
```
Primary: #2c5364
Secondary: #203a43
Accent: #0f2027
```

## 📊 Features by Role

### Admin Users Can:
- View full dashboard
- Manage servers
- Manage users
- Approve/reject lab requests
- View all charts & alerts
- Generate free access codes

### Regular Users Can:
- View dashboard (limited)
- View their servers
- Request lab extensions
- View charts & alerts
- Access terminal

## 🔧 Customization

### Change Brand Name:
Edit `includes/sidebar.php`:
```html
<span class="brand-text">Your Brand</span>
```

### Change Colors:
Edit `includes/header.php` CSS variables:
```css
:root {
  --primary-color: #your-color;
  --secondary-color: #your-color;
  --accent-color: #your-color;
}
```

### Add Logo:
Edit `includes/sidebar.php`:
```html
<a href="index.php" class="brand-link">
  <img src="path/to/logo.png" alt="Logo" class="brand-image">
  <span class="brand-text">KubeArena</span>
</a>
```

## 🔄 Migration Options

### Option 1: Test First
Access new pages with `_new.php` suffix:
- `login_new.php`
- `index_new.php`
- etc.

### Option 2: Gradual Migration
Run migration script in test mode:
```bash
chmod +x migrate.sh
./migrate.sh --test
```

### Option 3: Full Migration
Replace old files completely:
```bash
./migrate.sh --full
```

## 📱 Browser Support
- Chrome/Edge: ✅ Full support
- Firefox: ✅ Full support
- Safari: ✅ Full support
- Mobile browsers: ✅ Responsive

## 🐛 Common Issues

**Dark mode not working?**
- Clear cookies
- Check browser console
- Ensure JavaScript is enabled

**Charts not loading?**
- Check internet connection (CDN required)
- Verify Chart.js CDN URL
- Check browser console for errors

**Sidebar not responsive?**
- Ensure jQuery loads first
- Check AdminLTE JS is loaded
- Clear browser cache

## 📚 Documentation

- Full details: `MIGRATION_GUIDE.md`
- AdminLTE docs: https://adminlte.io/docs/3.2/
- Chart.js docs: https://www.chartjs.org/
- DataTables docs: https://datatables.net/

## 🎯 Phase Implementation Status

| Phase | Status | Files |
|-------|--------|-------|
| Phase 1 - Foundation | ✅ Complete | includes/, login_new.php, register_new.php |
| Phase 2 - Dashboard | ✅ Complete | index_new.php, charts_new.php, alerts_new.php, users_new.php |
| Phase 3 - UX Polish | 🔄 Ready | Animations, toasts, mobile |
| Phase 4 - Enterprise | 📋 Planned | Advanced features, exports |

## 💡 Tips

1. **Test in different browsers** before full deployment
2. **Check mobile view** - AdminLTE is fully responsive
3. **Customize colors** to match your brand
4. **Enable dark mode** for better UX
5. **Use DataTables export** for reports

## 🆘 Support

Issues? Check:
1. Browser console for JavaScript errors
2. Network tab for failed CDN requests
3. PHP error logs for backend issues
4. Database connectivity

---

**Version**: 3.0.0  
**Last Updated**: January 2026  
**Status**: Production Ready ✅

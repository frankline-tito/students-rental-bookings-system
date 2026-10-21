## . The Three Modules
### 👑 Admin
- Full control panel with system-wide stats
- Approve / reject / delete landlord accounts
- Approve / reject / delete house listings (or add one on behalf of a landlord)
- View, suspend, reinstate or delete student accounts
- Confirm or reject landlord listing-fee payments (M-Pesa / cash / bank)
- View and manage every booking in the system

### 🏠 Landlord
- Register, then wait for admin approval
- Pay a one-time listing fee (M-Pesa, cash or bank) before posting houses
- Add/edit/delete house listings with multiple photos, price, room type,
  location, nearest campus, amenities, and an optional Google Maps link
- Set **M-Pesa number, Paybill + account, bank name/account & account name** —
  shown to students on every listing so payment is simple and direct
- View and confirm/reject student booking requests
- Manage their own profile & password

### 🎓 Student / Tenant
- Register and log in immediately (no approval needed to browse)
- Search & filter houses by location, room type and max price
- View full house details: photos, price, amenities, map link, landlord's
  payment details, and reviews
- Request a booking, track its status (pending/confirmed/rejected) and cancel
  pending requests
- Save houses to a wishlist
- Leave a star rating + review after viewing a house
- Manage their own profile, photo & password

### 🔐 Shared / extra features
- Secure password hashing (`password_hash`) and session-based auth
- "Forgot password" flow with a 6-digit reset code (shown on-screen for this
  offline demo — wire up an SMTP/SMS provider for production)
- Fully responsive Bootstrap 5 UI with a custom brand theme
- Flash messages for every action (success/warning/error)

---

## . Folder Structure

```
rental-system/
├── admin/              Admin module pages
├── landlord/           Landlord module pages
├── student/            Student module pages
├── auth/               Register / login / logout / password reset
├── includes/           Shared header, navbar, footer, helper functions
├── config/db.php       Database connection
├── database/schema.sql Database schema (import this first)
├── database/seed_admin.php  One-time admin creation script (delete after use)
├── assets/css/style.css     Custom styling
├── assets/js/script.js      Small JS helpers (gallery, confirm dialogs)
├── assets/uploads/          Uploaded house & profile photos (auto-created)
├── index.php            Homepage
├── houses.php            Browse/search houses
└── house_details.php     Single house page (booking, wishlist, reviews)
```


  listing fee) — there's no live M-Pesa Daraja API call, which keeps the
  project simple, free to run, and demo-safe. This is a good point to mention
  as a "future enhancement" in your report/viva.
- All future enhancements you could mention: SMS/email OTP delivery (currently
  shown on-screen for demo), live M-Pesa STK Push integration, an admin
  analytics chart, and a map view of all houses.

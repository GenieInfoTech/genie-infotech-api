# Frontend-Backend Integration & Cleanup Plan

## Overview

This plan migrates the frontend from using its own Next.js API routes + SQLite database to using the Laravel backend API for all data operations. All email handling will be done by Laravel.

---

## Current Architecture (BEFORE)

```
Frontend (Next.js)                    Backend (Laravel)
├── /api/contact → SQLite            ├── /api/contact → MySQL (UNUSED)
├── /api/leads → SQLite              ├── /api/leads → MySQL (UNUSED)
├── Nodemailer → SMTP                ├── Queue Jobs → SMTP
├── Prisma ORM                       ├── Eloquent ORM
└── dev.db (SQLite)                  └── genie_api (MySQL)

Problem: Two separate databases, frontend handles emails, data not synced
```

## Target Architecture (AFTER)

```
Frontend (Next.js)                    Backend (Laravel)
├── submitContactForm() ─────────────→ POST /api/contact
├── submitLead() ─────────────────────→ POST /api/leads
├── No database                       ├── MySQL (genie_api)
├── No email handling                 ├── Email via Queue Jobs
└── Pure static + API calls           └── Filament Admin Panel

Benefit: Single source of truth, proper email handling, admin sees all data
```

---

## Phase 1: Files to DELETE from Frontend

### API Routes (Remove entire folder)
```
src/app/api/
├── contact/route.ts    ← DELETE (110 lines)
├── leads/route.ts      ← DELETE (86 lines)
└── estimate/route.ts   ← DELETE (91 lines) [Optional - client-side safe]
```

### Database Files (Remove entire folder)
```
prisma/
├── schema.prisma       ← DELETE
├── dev.db              ← DELETE
└── migrations/         ← DELETE (if exists)

prisma.config.ts        ← DELETE (root level)
src/lib/prisma.ts       ← DELETE
```

### Email Configuration
```
src/lib/email.ts        ← DELETE (32 lines)
```

**Total: 8-9 files to delete**

---

## Phase 2: Code to UPDATE in Frontend

### File: `src/lib/api.ts`

#### Remove unused functions (lines 91-130):
```typescript
// DELETE THESE FUNCTIONS:
export async function fetchBlogPosts(...) { ... }  // Lines 91-115
export async function fetchBlogPost(...) { ... }   // Lines 120-130
```

#### Update API URL logic:
```typescript
// BEFORE (line 8):
const API_URL = process.env.NEXT_PUBLIC_API_URL || '';

// AFTER:
const API_URL = process.env.NEXT_PUBLIC_API_URL;
if (!API_URL) {
  throw new Error('NEXT_PUBLIC_API_URL environment variable is required');
}
```

#### Update submitLead function (line 65):
```typescript
// BEFORE:
const endpoint = getApiUrl('/contact');  // Wrong - goes to contact

// AFTER:
const endpoint = getApiUrl('/leads');    // Correct - goes to leads
```

### File: `src/lib/analytics.ts`

#### Remove unused functions:
```typescript
// DELETE THESE (never called anywhere):
export function trackScrollDepth() { ... }         // Line 203
export function trackServiceInterest() { ... }     // Line 251
export function trackConversionComplete() { ... }  // Line 272
```

---

## Phase 3: Environment Variables

### File: `.env` - UPDATE

```bash
# REMOVE THESE:
DATABASE_URL="file:./dev.db"
SMTP_HOST="..."
SMTP_PORT="..."
SMTP_USER="..."
SMTP_PASSWORD="..."

# KEEP THESE:
NEXT_PUBLIC_SITE_URL="http://localhost:3000"
NEXT_PUBLIC_GA_MEASUREMENT_ID="G-DF2ERGF9VX"

# ADD THIS (REQUIRED):
NEXT_PUBLIC_API_URL="http://127.0.0.1:8000/api"
```

### File: `.env.example` - UPDATE

```bash
# Site Configuration
NEXT_PUBLIC_SITE_URL="http://localhost:3000"

# Laravel Backend API (REQUIRED)
NEXT_PUBLIC_API_URL="http://127.0.0.1:8000/api"

# Google Analytics 4
NEXT_PUBLIC_GA_MEASUREMENT_ID="your-ga-measurement-id"
```

### Production `.env` values:
```bash
NEXT_PUBLIC_SITE_URL="https://genieinfo.tech"
NEXT_PUBLIC_API_URL="https://api.genieinfo.tech/api"
NEXT_PUBLIC_GA_MEASUREMENT_ID="G-DF2ERGF9VX"
```

---

## Phase 4: Dependencies to REMOVE

### File: `package.json`

```json
// REMOVE from dependencies:
"@prisma/client": "^6.19.0",
"nodemailer": "^7.0.12",

// REMOVE from devDependencies:
"prisma": "^6.19.0",
"@types/nodemailer": "^7.0.4",
```

After removing, run:
```bash
npm install
```

---

## Phase 5: Backend API Endpoints (Already Ready)

| Frontend Action | Backend Endpoint | Method | Auth |
|-----------------|------------------|--------|------|
| Contact form submit | `/api/contact` | POST | No |
| Exit intent popup | `/api/leads` | POST | No |
| (Future) Blog list | `/api/blog` | GET | No |
| (Future) Blog post | `/api/blog/{slug}` | GET | No |

### Backend Email Flow (Automatic):
```
Lead submitted → Laravel creates Lead in MySQL
              → Dispatches SendLeadNotification job (to team)
              → Dispatches SendWelcomeEmail job (to lead)
              → All tracked in email_logs table
              → Visible in Filament admin panel
```

---

## Phase 6: Files Summary

### Frontend Files to DELETE (8 files):
| File | Lines | Reason |
|------|-------|--------|
| `src/app/api/contact/route.ts` | 110 | Moving to Laravel |
| `src/app/api/leads/route.ts` | 86 | Moving to Laravel |
| `src/app/api/estimate/route.ts` | 91 | Optional (client-side safe) |
| `src/lib/email.ts` | 32 | Email handled by Laravel |
| `src/lib/prisma.ts` | 12 | No more frontend DB |
| `prisma/schema.prisma` | 72 | No more frontend DB |
| `prisma/dev.db` | - | SQLite database file |
| `prisma.config.ts` | 14 | Prisma config |

### Frontend Files to MODIFY (4 files):
| File | Changes |
|------|---------|
| `src/lib/api.ts` | Remove blog functions, fix submitLead endpoint |
| `src/lib/analytics.ts` | Remove 3 unused functions |
| `.env` | Remove DB/SMTP vars, add API_URL |
| `.env.example` | Same as above |
| `package.json` | Remove 4 dependencies |

### Frontend Files NO CHANGES NEEDED:
| File | Reason |
|------|--------|
| `src/components/sections/ContactSection.tsx` | Already uses `submitContactForm()` |
| `src/components/ui/ExitIntentPopup.tsx` | Already uses `submitLead()` |
| `src/components/ai/InstantEstimator.tsx` | Client-side only, no API |

---

## Phase 7: Testing Checklist

### After cleanup, test:
- [ ] Contact form submission → Check Laravel admin for new lead
- [ ] Contact form → Verify team receives notification email
- [ ] Contact form → Verify lead receives welcome email
- [ ] Exit intent popup → Check Laravel admin for new lead
- [ ] Exit intent popup → Verify emails sent
- [ ] Google Analytics → Events still tracking
- [ ] Build succeeds → `npm run build`
- [ ] No console errors on pages

### Backend verification:
```bash
# Check leads in database
php artisan tinker --execute="echo \App\Models\Lead::count();"

# Check email logs
php artisan tinker --execute="echo \App\Models\EmailLog::count();"

# Process queue (if using database queue)
php artisan queue:work
```

---

## Execution Order

1. **Update `.env`** - Add `NEXT_PUBLIC_API_URL=http://127.0.0.1:8000/api`
2. **Update `src/lib/api.ts`** - Fix endpoint and remove unused code
3. **Test forms work** - Submit test lead, verify in Laravel admin
4. **Delete API routes** - Remove `src/app/api/` folder
5. **Delete database files** - Remove `prisma/` folder and related
6. **Delete email file** - Remove `src/lib/email.ts`
7. **Update `package.json`** - Remove dependencies
8. **Run `npm install`** - Clean up node_modules
9. **Run `npm run build`** - Verify build succeeds
10. **Final testing** - All forms, emails, analytics

---

## Risk Assessment

| Risk | Impact | Mitigation |
|------|--------|------------|
| Forms break | High | Test thoroughly before deleting API routes |
| Emails not sent | Medium | Verify Laravel queue is processing |
| Build fails | Medium | Fix TypeScript errors from removed imports |
| Data loss | Low | SQLite data can be migrated if needed |

---

## Rollback Plan

If issues occur:
1. Git revert to previous commit
2. Restore deleted files from git history
3. Restore `.env` variables
4. Run `npm install` to restore dependencies

---

## Questions Before Proceeding

1. **Keep estimate API?** - It's client-side only, doesn't store data. Keep or remove?
2. **Migrate SQLite data?** - Any existing leads in frontend SQLite to migrate to MySQL?
3. **Blog integration?** - Want to add blog pages to frontend using Laravel API?

---

**Ready to proceed? I'll execute this plan step by step.**

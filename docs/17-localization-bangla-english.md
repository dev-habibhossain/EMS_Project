# 17 — Localization (Bangla + English)

## Architecture

- Laravel `lang/en` and `lang/bn` JSON or PHP files.
- Vue `$page.props.locale` + i18n helper wrapping those strings shared via Inertia middleware (`translations` share for current file set, or load JSON).
- User `locale` column; header switcher posts to `/locale`.

## Labels

Every nav item, button, validation message translated. Product **data** remains as entered (name + name_bn fields).

## Formats

- Timezone `Asia/Dhaka`.
- Display dates `DD-MM-YYYY`.
- Money: `৳ 1,25,000.00` option (Indian grouping) vs `৳ 125,000.00`. **Assumption A-I18N-1:** store raw decimal; format with a small helper supporting `en-BD` grouping.
- Invoice language independent of UI language (print option).

## PDFs / print

Use a font that contains Bengali. Test wrapping. Numbers may stay Western digits unless `bn` digit setting on (default Western digits — more readable for amounts).

## Currency

ISO BDT, symbol ৳. No multi-currency in MVP.

# UI/UX Specification — Tile & Ceramic ERP + POS

**Status:** Implementation-ready design spec  
**Does not change:** PRD, inventory math, schema, roles, or workflows  
**Does not include:** Vue, Tailwind class lists, HTML, or application code  

---

## 0. Design research synthesis

Studied (not copied): enterprise inventory tables, ERP density systems, real POS layout practice (search + cart + totals/tender), warehouse operations queues, accounting statement conventions (running balance, debit/credit columns).

| Pattern seen | Keep / drop | Adaptation here |
|---|---|---|
| Dense tables, sticky header, right-aligned money | Keep | Default density `compact` for ops tables |
| Filter bar above table, saved views | Keep | Warehouse + lot filters first-class |
| Huge gradient KPI cards + donut charts | Drop | Small metric row; work queues instead |
| Card grids for SKUs | Drop for inventory | Optional tile *thumb* only on POS results |
| Glassmorphism / purple SaaS | Drop | Oxide + ink on paper |
| 3-pane POS (find → cart → pay) | Keep | Lot/unit step inserted without extra pages |
| Role-specific dashboards | Keep lightly | Same shell; widgets hide by permission |
| 3D warehouse maps | Drop | List + warehouse filter |
| Visual catalog galleries | Partial | POS result may show size chip, not a gallery |

**Product name in UI chrome:** company name from Settings. Internal design-system name: **Kiln**.

---

## 1. Design personality

**Kiln** is a workshop tool: clay-dust practical, not a marketing site.

- Professional, slightly warm (ceramic), never playful
- Premium through typography and alignment, not glow
- Information-rich; density over whitespace
- Built for 8-hour counter and godown sessions
- Distinctive via **oxide accent + dual-unit stock cells**, not via illustration

Anti-goals: Dribbble decoration, oversized radius, hero charts, empty-state illustrations that waste a shift.

---

## 2. Design system — color

Identity hue is **fired oxide** (tile kiln), not indigo. Neutrals are ink on warm paper.

### Light mode

| Token | Hex | Use |
|---|---|---|
| `--bg-app` | `#F3EFE8` | App canvas (warm paper) |
| `--bg-sidebar` | `#1C1916` | Sidebar (near-black umber) |
| `--bg-sidebar-hover` | `#2A2622` | Nav hover |
| `--bg-header` | `#FFFcf8` | Top bar |
| `--surface` | `#FFFcf8` | Pages, tables, forms |
| `--surface-elevated` | `#FFFFFF` | Modals, popovers |
| `--surface-muted` | `#EBE6DD` | Table header |
| `--border` | `#D9D1C4` | Hairline structure |
| `--border-strong` | `#B8AD9C` | Inputs focused ring pair |
| `--text` | `#1C1916` | Primary text |
| `--text-muted` | `#6B645B` | Labels, meta |
| `--text-inverse` | `#F3EFE8` | On sidebar |
| `--primary` | `#B44422` | Oxide — primary buttons, active nav tick |
| `--primary-hover` | `#97381C` | |
| `--primary-subtle` | `#F6E4DC` | Selected row, chips |
| `--focus` | `#1F6B5A` | Teal focus ring |
| `--success` | `#2F7D4A` | Posted, in-stock |
| `--warning` | `#B7791F` | Low stock, partial, due |
| `--danger` | `#B42318` | Error, damaged, overdue |
| `--info` | `#2B5F8A` | Info banners |
| `--row-hover` | `#F7F1E8` | |
| `--row-selected` | `#F6E4DC` | |
| `--link` | `#8F3419` | In-page links |

Sidebar text inactive: `#C4B8A8`. Active item: surface `#2A2622` + oxide left bar 3px.

### Dark mode

Do not invert. Recessed charcoal workshop:

| Token | Hex |
|---|---|
| `--bg-app` | `#161412` |
| `--bg-sidebar` | `#100E0C` |
| `--bg-header` | `#1C1916` |
| `--surface` | `#1C1916` |
| `--surface-elevated` | `#24201C` |
| `--surface-muted` | `#2A2622` |
| `--border` | `#3A342E` |
| `--text` | `#EDE6DB` |
| `--text-muted` | `#A39A8E` |
| `--primary` | `#D36A48` |
| `--primary-subtle` | `#3A221A` |
| `--focus` | `#4AA38C` |
| `--success` | `#5CA572` |
| `--warning` | `#D4A054` |
| `--danger` | `#E86A60` |
| `--info` | `#6A96C2` |

Charts (rare): oxide, umber, teal, clay — never rainbow.

Status badges: text + 8% tint background + 1px border of the same hue. No heavy-fill pills.

---

## 3. Typography

| Role | Font | Notes |
|---|---|---|
| UI Latin | **IBM Plex Sans** | Excellent tables |
| Bangla | **Noto Sans Bengali** | `font-family: "IBM Plex Sans", "Noto Sans Bengali", sans-serif` |
| Numerals / money / SQFT | **IBM Plex Sans tabular** (`font-variant-numeric: tabular-nums`) | Mandatory in tables |

Bangla is first-class: do not shrink BN labels; allow wrap. Table headers may use shorter BN strings from lang files.

Scale (root 14px):

| Token | Size | Weight | Line | Use |
|---|---|---|---|---|
| `display` | 22px | 600 | 1.25 | Rare page titles |
| `h1` | 18px | 600 | 1.3 | Page title |
| `h2` | 15px | 600 | 1.35 | Section |
| `body` | 14px | 400 | 1.45 | Forms, copy |
| `label` | 12px | 500 | 1.3 | Field labels |
| `table` | 13px | 400 | 1.3 | Cells |
| `table-head` | 11px | 600 | 1.2 | EN headers may be small caps; BN sentence case 12px/600 |
| `numeric` | 13px | 500 | 1.2 | Qty, money |
| `caption` | 12px | 400 | 1.35 | Hints |

Page titles never exceed 18px. Do not use 32px dashboard headlines.

---

## 4. Spacing and layout

Scale: 4 / 8 / 12 / 16 / 24 / 32 (no 64px empty bands).

| Element | Spec |
|---|---|
| Sidebar expanded | 232px |
| Sidebar collapsed | 56px |
| Header | 48px, border-bottom |
| Page padding | 16px 20px |
| Content max | none for tables; forms max 960px |
| Section gap | 16px |
| Card padding | 12–16px (use cards sparingly) |
| Form field gap | 12px vertical; 16px column gap |
| Modal | 480 / 640 / 880px; max-height 85vh |
| Drawer | 420px right |
| Table row compact | 36px |
| Table row default | 40px |
| POS | full-bleed, no sidebar |

Elevation: **borders, not shadows.** 1px `--border`. Modal: 1px border + overlay `rgba(28,25,22,0.45)`.

Radius: 4px controls, 6px panels. Not 16–24px.

---

## 5. Application shell

### Sidebar (all modules except POS and print)

Groups (icons: 16px stroke):

1. **Work** — Dashboard, POS  
2. **Catalog** — Products, Brands, Factories, Sizes, Shades, Grades, Batches  
3. **Stock** — Inventory, Warehouses, Transfers, Damaged  
4. **Trade** — Sales, Purchases, Challans, Returns  
5. **Parties** — Customers, Suppliers  
6. **Money** — Payments  
7. **Insights** — Reports  
8. **System** — Users, Settings, SMS log, Activity  

Hide items without view permission. Do not show locked padlock items in main nav.

Active: 3px oxide bar + muted umber fill. Hover: fill only. Collapsed: icons + tooltip.

Badge on Transfers if pending receive count > 0; on Challans if draft/dispatch queue.

### Header

Left: collapse control, breadcrumb (Module / Record).  
Right: global search (`/` or `Ctrl+K`), language `EN | বাং`, theme, notifications, user menu (name, role, warehouse, logout).

Contextual page actions live in the **page header row**: title left, primary + secondary buttons right.

---

## 6. Dashboard (tile business, not SaaS)

Owner / Admin / Manager: full. Sales: today sales + own recent. Warehouse: pending receive/dispatch + low stock. Accountant: due/payable/collections.

**Do not** use 8 equal gradient stat cards and 3 charts.

```
[ Page: Overview ]                         [ Warehouse ▾ ] [ Today ▾ ]

TODAY STRIP (single 40px row, not cards)
  Sales ৳…   Collected ৳…   Due opened ৳…   Purchases ৳…   Invoices n

WORK QUEUES (two columns)
  Left: Attention
    - Low stock (count → list)
    - Pending transfer receives
    - Open challans to dispatch
  Right: Recent documents
    - Last 8 sales
    - Last 8 purchases

BOTTOM
  Top products today (name, sqft, ৳) | Customers with highest due (5)
```

Charts: none required. One optional 14-day sparkline only if cheap. No profit widget until cost snapshot exists.

---

## 7. Navigation IA

Rare masters live under Products or Settings. Units are system-seeded — no daily sidebar item.

POS is a **mode**: opening POS replaces the shell (Escape or “Exit POS” returns).

---

## 8. Component system

### Buttons
- Primary: oxide fill, white text, 32px height default, 28px in tables  
- Secondary: surface + border  
- Ghost: text only  
- Danger: danger fill only in confirm dialogs  
- Disabled: 40% opacity; title “No permission”

### Inputs
Height 32px. Label above. Required asterisk. Hint 12px muted. Error: border danger + text under field. Searchable select for products, parties, lots.

### Unit quantity control (`UnitQtyInput`)
Segmented unit `BOX | PCS | SQFT` + numeric input + live conversion caption:  
`5 BOX = 20 PCS = 53.80 SQFT`  
Warn if SQFT does not map to whole pieces.

### Lot picker
Drawer or popover: batch / shade / quality / warehouse / availability in **triple qty**. If one lot, auto-select and skip UI.

### Tables
See §9.

### Filters
Horizontal wrap bar: search + 3–5 selects + “More”. Apply on change for selects; debounce search 200ms.

### Tabs
Underline, 13px, used on product, customer, sale detail.

### Cards
Avoid wrapping every table in a card. Use a bordered surface once.

### Badges
`draft` muted, `posted` success, `partial` warning, `cancelled` danger outline, `damaged` danger, `sellable` success faint.

### Modal / drawer / confirm
Destructive: “Reason” textarea required for cancel invoice.

### Toast
Top-right, 4s, one line + action “View invoice”.

### Empty
One sentence + primary action. No illustration.

### Loading
Table: header visible + 8 gray lines. POS search: 4-row skeleton. Never full-page spinner except first login.

### Command search
Results grouped: Products, Customers, Sales, Purchases.

---

## 9. Data tables

- Sticky header; horizontal scroll inside page  
- Compact 36px rows  
- Text left; **qty, SQFT, money right**  
- First column: primary identity (SKU or doc number)  
- Sort on server columns only  
- Pagination: `25 / 50 / 100`  
- Row click → detail; `⋯` for secondary  
- Bulk select only where a bulk action exists  
- Inventory may expand to per-lot / last movements  
- Zebra off; hairline row borders  
- Density toggle: Compact / Comfortable  

**Stock cell pattern:**

```
10.00 BOX
40 PCS
107.60 SQFT
```

Three lines, tabular nums, muted second/third.

---

## 10. Forms

- Group fields in labeled sections (Identity, Conversion, Pricing, Flags)  
- Two columns on ≥1280px; one column below  
- Product create is **one page with sections**, not a 5-step wizard  
- Dependent fields: if `requires_shade` off, hide shade on stock docs  
- Save / Cancel sticky in page header  
- Unsaved guard on navigate  
- Inline validation on blur  

---

## 11. UX states

| State | Treatment |
|---|---|
| Initial | Layout + skeleton |
| Empty | Sentence + CTA |
| Populated | Table |
| Searching | Keep previous + faint progress on search box |
| No results | Clear explanation + clear filters |
| Validation | Field errors; summary if >3 |
| Server error | Banner retry |
| Success | Toast + stay or redirect to detail |
| 403 | “You cannot adjust stock.” + Exit |
| Destructive | Modal + reason |

---

## 12. Responsive

| Break | Behavior |
|---|---|
| ≥1440 | Sidebar + full tables |
| 1280–1439 | Compact sidebar optional |
| 1024–1279 | Collapsed sidebar; tables scroll-x |
| Tablet | POS still 2-pane if width ≥900 |
| Mobile | Lists stay tabular with horizontal scroll. Ledger stays a table. Not a primary POS device. |

Do not card-ify inventory on mobile.

---

## 13. Bangla + English

- Switcher in header; persists on user  
- BN strings 20–40% longer: buttons `min-width`, never truncate mid-akhar  
- Table headers wrap to 2 lines  
- Invoice/challan language is a **print control**, independent of UI locale  
- Currency `৳` prefix; BN print may use lakh grouping  
- Numerals: Western digits default  

---

## 14. Light + dark

User toggle in header. Tables, badges, borders retokenized. Oxide remains identifiable on both.

---

## 15. Accessibility

- Visible 2px `--focus` ring  
- Tab order: skip to content, filters, table, page actions  
- Comboboxes keyboardable  
- Contrast ≥ 4.5:1  
- Errors associated with fields  
- POS: `/` focuses search; `F2` pay; `Esc` closes modal  

---

## 16. Micro-interactions

Allowed: 120ms hover; modal fade 100ms; toast slide.  
Forbidden: parallax, page transitions, bouncing KPIs.

---

## 17. Roles in the UI

Hide nav and hide/disable actions. Disabled tooltip = plain-language permission. Cost columns omitted for Salesperson. Warehouse has no POS. Accountant dashboard omits POS CTA. Backend remains authority.

---

# 18. Screen inventory (design-level)

### S-AUTH-01 Login
Centered 360px form on paper bg; company name; no marketing hero. Fields: email/username, password, remember.

### S-DASH-01 Overview
See §6.

### S-PRD-01 Product list
Filters: search, brand, factory, size, active.  
Columns: SKU, Name, Size, Brand, Pcs/Box, Sqft/Pc, Price/Box, On-hand (triple), Status.

### S-PRD-02 Create / edit
Sections: Identity → Conversion (live sqft/box preview) → Pricing per BOX/PCS/SQFT → Tracking flags. Warn if editing factors while stock exists.

### S-PRD-03 Product detail
Tabs: Overview | Stock by lot | Prices | Movements | Documents.

### S-INV-01 Inventory overview
Filters: warehouse, product, batch, shade, quality, condition, low stock.  
Columns: Product+SKU, Size, WH, Batch, Shade, Grade, Condition, BOX, PCS, SQFT.  
Actions: Adjust, Move.

### S-INV-02 Stock movements
Drawer: lot header + movement table. Immutable.

### S-INV-03 Adjustment modal
Lot, unit, qty, direction or new count, reason. Preview after qty.

### S-INV-04 Damaged
Inventory table with condition=damaged. Record damage from sellable lot.

### S-WH-01 / S-WH-02 Warehouse list + detail
Detail tabs: Stock | Transfers | Adjustments.

### S-WH-03 / S-WH-04 Transfer list + pipeline
Statuses: draft → dispatched → received. Recap: source −, in-transit +, dest 0 until receive.

### S-POS-01 Point of sale
Full-screen, no app sidebar.

```
| SEARCH + RESULTS (40%) | CART (35%) | TENDER (25%) |
```

Walk-in + due > 0: Pay disabled. After success: print preview — Print / New sale.

### S-SALE-01 / 02 Sales list + detail
No edit of posted body. Actions: Print, Challan from sale, Return, Cancel.

### S-PUR-01 / 02 Purchase list + create/receive
Receive panel: qty this receipt, batch, shade, quality, split damaged qty.

### S-CUS-01 / 02 / 03 Customers, profile, ledger
Ledger columns: Date, Particulars, Ref, Debit, Credit, Balance. Advance in info color, not red.

### S-SUP-01 / 02 Suppliers
Mirror customers; payable instead of due. Hidden from Sales nav.

### S-PAY-01 Payments journal
New receipt / supplier payment. Reverse with permission.

### S-RET-01 / 02 Return wizards
Impact summary: stock and money.

### S-CHL-01 / 02 Challans
UI note: stock already deducted on invoice (default mode). Print: logo, lines with batch/shade, signatures.

### S-RPT-01 Reports
Hub + typed report: filters, summary strip, table, CSV, print. Charts only when useful.

### S-USR-01 Users
Name, role, warehouse, active.

### S-SET-01 Settings
Company, documents, inventory flags, locale, SMS credentials masked, POS defaults.

### S-SMS-01 SMS log
To, template, status, time, error.

### S-AUD-01 Activity
Filter user, action, date.

---

## 19. Text wireframes

### Dashboard

```
+-- sidebar --+------------------------------------------------------+
| Work        | Overview                    [Showroom] [Today]        |
|  Overview   |------------------------------------------------------|
|  POS        | Sales ৳…  Collected ৳…  Due ৳…                       |
| Stock       | Purchases ৳…   Bills n                                |
| ...         |------------------------------------------------------|
|             | ATTENTION              | RECENT SALES                 |
|             | low stock / transfers  | INV-104  Rahman  ৳… Due      |
+-------------+------------------------------------------------------+
```

### Inventory

```
Inventory                              [Adjust] [Export]
[Search] [WH] [Brand] [Shade] [Sellable ▾] [Low]

SKU / Product     Size    WH     Batch  Shade  G  Cond   BOX    PCS     SQFT
RAK-60-WHT        600     Show   B-04   A2     A  Sell   10.00  40.00  107.60
```

### POS

```
POS  WH:Showroom   Customer: Walk-in ▾              [Exit]
+----------------------+------------------+----------------+
| Search ____________  | CART             | Tender         |
| results + lot + unit | lines + totals   | TOTAL / DUE    |
| [Add]                |                  | [Pay F2]       |
+----------------------+------------------+----------------+
```

### Product form

```
New product                                         [Cancel] [Save]
Identity        SKU  Names EN/BN  Brand Factory Size Barcode
Conversion      Pcs/box  Sqft/piece   Preview 1 BOX = …
Pricing         /BOX /PCS /SQFT
Tracking        requires batch/shade  default grade  active
```

### Customer ledger

```
Rahman Ceramics     Balance ৳40,000    Limit ৳2,00,000
Date    Particulars           Debit        Credit       Balance
```

### Transfer

```
TR-018  Showroom → Godown-2     Status: Dispatched
Effect: Source sellable −SQFT | In-transit +SQFT | Dest 0
                         [Receive] [Cancel]
```

---

## 20. Print

A4 invoices/challans: 12px, logo, company, party, line table with batch/shade, totals in ৳. Thermal 80mm stacked. Language from print dialog. Amount-in-words is Could Have — omit in MVP.

---

## 21. Flags (do not invent)

1. Amount-in-words — omit MVP print.  
2. Product images — no gallery unless an image field exists.  
3. Barcode hardware — search field as keyboard wedge only.  
4. Warehouse-scoped users — Should Have.  
5. Hold cart — not MVP.

---

## 22. Implementation notes (no code)

- Tokens as CSS variables matching §2–4.  
- `AppLayout` and `PosLayout`.  
- Reuse `UnitQtyInput`, `LotPicker`, `Money`, `StockTriple`, `DocumentStatus`.  
- If a mockup looks like a gradient analytics template, it is wrong.

---

*End of UI/UX specification.*

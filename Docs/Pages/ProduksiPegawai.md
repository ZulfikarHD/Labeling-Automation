# ProduksiPegawai.vue Documentation

## Overview

**Modern tab-based dashboard untuk monitoring produksi harian pegawai dalam sistem labeling**

Komponen Vue ini menampilkan interface monitoring yang telah didesain ulang dari table-based menjadi modern dashboard dengan tab navigation, ranking system, dan gamification elements. Halaman ini menggunakan arsitektur hybrid dimana data dasar workstation dikirim dari controller, sedangkan data dinamis (tim aktif, data produksi) di-fetch melalui API calls.

## Purpose

Menyediakan interface monitoring produksi dengan fitur:
- **Tab-based Navigation**: Interface dengan tab untuk overview, ranking, dan detail per tim
- **Dashboard Overview**: Summary cards dengan metrics keseluruhan dan team performance cards
- **Ranking System**: Competitive leaderboard dengan top performers dan team rankings
- **Individual Employee Cards**: Detail performa per pegawai dengan ranking numbers (1/17 format)
- **Employee Ranking**: Sorted employee display dengan urutan performance dalam tim
- **Gamification Elements**: Achievement badges, progress bars, dan competitive features
- **Date picker filtering**: Filter data berdasarkan tanggal
- **Real-time data loading**: Dynamic data fetching untuk semua tim aktif
- **Modern UI/UX**: Card-based design dengan visual indicators dan responsive tabs
- **Dark mode support**: Complete dark theme implementation dengan slate/indigo color scheme
- **Mobile responsive**: Tab wrapping dan mobile-first design
- **AuthenticatedLayout Integration**: Seamless integration dengan aplikasi layout

## Component Structure

### Script Setup (Composition API)
- **Props**: Menerima data teams dari controller
- **Reactive State**: Form data, loading states, active teams, dan teamData
- **API Integration**: Fetch data dinamis dari multiple endpoints
- **Computed Properties**: Filter teams, ranking calculations, dan statistics
- **Tab Management**: Active tab state dan navigation logic
- **Lifecycle Hooks**: Initialize data saat component mount

### Template Structure
- **Layout**: AuthenticatedLayout integration (no duplicate backgrounds)
- **Header**: Title dan date picker dengan slate color scheme
- **Tab Navigation**: Responsive wrapping tabs (Overview, Ranking, Team tabs)
- **Content**: Dynamic tab content dengan conditional rendering
- **Loading**: Overlay component untuk loading states
- **Empty State**: Message ketika tidak ada data

## Props

### `teams`
**Type**: `Object`
**Description**: Data workstation dari controller
**Structure**:
```javascript
{
    teams: [
        {
            id: 1,
            workstation: "Team A"
        },
        {
            id: 2,
            workstation: "Team B"
        }
        // ... more teams
    ]
}
```

## Reactive State

### Form Data
```javascript
const form = useForm({
    date: getDate.toISOString().substr(0, 10) // Format YYYY-MM-DD
})
```

### Component State
```javascript
const activeTeams = ref([])         // Array ID tim yang aktif
const isLoading = ref(true)         // Loading state
const activeTab = ref('overview')   // Current active tab
const teamData = ref({})            // Comprehensive team data storage
```

## Methods

### `fetchActiveTeams()`
**Purpose**: Mengambil data tim yang aktif pada tanggal tertentu dan fetch comprehensive team data

**Flow**:
1. Set loading state ke true
2. Call API `/api/active-teams` dengan parameter date
3. Update activeTeams dengan response data
4. Call `fetchAllTeamData()` untuk comprehensive data
5. Handle error jika terjadi masalah
6. Set loading state ke false

### `fetchAllTeamData()`
**Purpose**: Mengambil data lengkap untuk semua tim aktif secara parallel

**Flow**:
1. Create promises array untuk semua tim aktif
2. Parallel fetch team name dan production data
3. Store results dalam teamData reactive object
4. Handle individual team errors gracefully

### `getTeamEmployeesWithRanking(teamId)`
**Purpose**: Mendapatkan employees dengan ranking information untuk specific team

**Returns**: Array employees yang sudah sorted dengan ranking information:
```javascript
[
    {
        ...employeeData,
        rank: 1,
        totalEmployees: 17,
        verifikasiNumber: 25000
    }
    // ... more employees
]
```

## Computed Properties

### `filteredTeams`
**Purpose**: Filter workstation berdasarkan tim yang aktif
**Returns**: Array workstation yang memiliki aktivitas pada tanggal terpilih

### `overviewStats`
**Purpose**: Calculate overall statistics untuk dashboard overview
**Returns**: Object dengan totalVerifikasi, totalRim, activeTeams, totalEmployees

### `getTopPerformers`
**Purpose**: Mendapatkan top 10 performers across all teams
**Logic**: Combines all employees, sorts by verification count, returns top 10 with team info

### `getTeamRanking`
**Purpose**: Ranking teams berdasarkan total verification output
**Returns**: Teams sorted by performance dengan percentage calculations

## Utility Functions

### `getTeamStats(teamId)`
**Purpose**: Calculate statistics untuk specific team
**Returns**: Object dengan verifikasi, rim, po, employees counts

### `getPerformanceColor(actual, target = 17500)`
**Purpose**: Dynamic color coding berdasarkan performance
**Returns**: CSS class string untuk color coding

### `getProgressPercentage(actual, target = 17500)`
**Purpose**: Calculate progress percentage terhadap target
**Returns**: Number (0-100) untuk progress bar width

### `getRankBadgeColor(rank)` & `getRankIcon(rank)`
**Purpose**: Styling dan icon untuk ranking badges dalam leaderboard
**Returns**: CSS classes dan emoji icons untuk top 3 ranks

## Tab Navigation System

### Tab Structure
1. **Overview Tab**: Dashboard dengan summary cards dan team performance
2. **Ranking Tab**: Leaderboard dengan top performers dan team rankings
3. **Dynamic Team Tabs**: Individual team detail views dengan employee cards

### Responsive Tab Behavior
- **Mobile**: Tab wrapping dengan abbreviated text ("Ring.", "Rank")
- **Desktop**: Horizontal layout dengan full text ("Ringkasan", "Ranking")
- **Overflow**: Horizontal scroll sebagai fallback

## Employee Cards Features

### Ranking Display
- **Format**: "1/17" (rank/total employees)
- **Label**: "Urutan" dalam bahasa Indonesia
- **Color**: Indigo theme untuk consistency
- **Position**: Top-right corner of employee card

### Card Information
- **Employee Name**: Prominent display
- **Ranking**: Position within team
- **Progress Bar**: Visual progress terhadap target dengan percentage
- **Statistics**: Verifikasi, RIM, PO counts
- **Target Reference**: 17.500 lembar target display

### Sorting Logic
Employees dalam setiap tim di-sort berdasarkan:
1. Verification count (descending)
2. Rank assignment (1, 2, 3, etc.)
3. Total team size calculation

## API Integration

### Active Teams Endpoint
**URL**: `/api/active-teams`
**Method**: GET
**Parameters**: `date` (YYYY-MM-DD format)
**Response**: Array of team IDs yang aktif pada tanggal tersebut

### Team Data Endpoints
- `/api/team-name/{id}` - Untuk nama tim
- `/api/pendapatan-harian` - Untuk data produksi dan verifikasi per tim

### Parallel API Calls
Component melakukan parallel fetching untuk optimal performance:
```javascript
const [teamResponse, produksiResponse] = await Promise.all([
    axios.get(`/api/team-name/${teamId}`),
    axios.get(`/api/pendapatan-harian?date=${form.date}&team=${teamId}`)
])
```

## Styling & Design

### Color Scheme (AuthenticatedLayout Compatible)
- **Primary**: Slate color palette (slate-50 to slate-900)
- **Accent**: Indigo theme (indigo-500, indigo-600)
- **Background**: Uses AuthenticatedLayout's gradient background
- **Cards**: White/slate-800 dengan slate borders
- **Text**: Slate color hierarchy untuk optimal readability

### Layout Structure
- **Container**: max-w-7xl untuk consistency dengan AuthenticatedLayout
- **Grid**: Responsive grid systems (1-4 columns based on breakpoint)
- **Spacing**: Consistent padding dan margins
- **No Duplicate Styling**: Removed conflicting backgrounds dan padding

### Responsive Design
- **Mobile-first**: Tab wrapping, abbreviated text, compact layouts
- **Breakpoints**: sm, md, lg, xl responsive behavior
- **Grid Systems**: Adaptive column counts
- **Touch-friendly**: Proper spacing untuk mobile interaction

## Gamification Elements

### Ranking System
- **Individual Leaderboard**: Top 10 performers dengan medal badges
- **Team Rankings**: Teams sorted by total output
- **Achievement Tracking**: Target achievement statistics

### Visual Elements
- **Medal Badges**: 🥇🥈🥉 untuk top 3 performers
- **Progress Bars**: Gradient progress indicators
- **Color Coding**: Performance-based color schemes
- **Achievement Cards**: Target achievement breakdown

### Competitive Features
- **Real-time Rankings**: Dynamic ranking updates
- **Performance Comparison**: Team vs team comparison
- **Target Tracking**: Progress towards daily targets

## Performance Considerations

### API Optimization
- **Parallel Requests**: Simultaneous team data fetching
- **Single Active Teams Call**: Efficient date-based filtering
- **Error Resilience**: Individual team failures don't break entire page

### Rendering Optimization
- **Computed Properties**: Efficient data transformations
- **Conditional Rendering**: Only active tab content rendered
- **Unique Keys**: Proper v-for keys untuk efficient re-rendering
- **Lazy Loading**: Team data only loaded when needed

## Recent Updates

### Version 2.0 Features
- **Complete UI Redesign**: From table-based to modern dashboard
- **Ranking System**: Employee ranking dengan "1/17" format display
- **Tab Navigation**: Responsive tab system dengan mobile wrapping
- **Gamification**: Leaderboards, achievements, competitive elements
- **AuthenticatedLayout Integration**: Seamless design consistency
- **Mobile Optimization**: Tab wrapping dan responsive improvements

### Breaking Changes
- **UI Structure**: Completely new interface design
- **Data Flow**: Enhanced data management dengan comprehensive team data
- **Navigation**: Tab-based instead of single-page layout
- **Styling**: AuthenticatedLayout integration requires updated color scheme

## Notes

- Component telah didesain ulang dari table-heavy interface menjadi modern dashboard
- Ranking system memberikan competitive element yang mendorong performance
- Tab wrapping ensures optimal mobile experience dengan responsive design
- AuthenticatedLayout integration provides seamless application consistency
- Employee cards dengan ranking numbers memberikan clear performance hierarchy
- Gamification elements membuat monitoring lebih engaging dan motivational
- Real-time data updates maintain accuracy untuk production monitoring
- Mobile-first approach ensures accessibility across all device types
- Component architecture supports future enhancements dan feature additions

# ProduksiPegawai.vue Documentation

## Overview

**Halaman monitoring produksi harian pegawai dalam sistem labeling**

Komponen Vue ini menampilkan interface untuk monitoring aktivitas produksi pegawai per tim dan tanggal. Halaman ini menggunakan arsitektur hybrid dimana data dasar workstation dikirim dari controller, sedangkan data dinamis (tim aktif, data produksi) di-fetch melalui API calls.

## Purpose

Menyediakan interface monitoring produksi dengan fitur:
- Date picker untuk filtering data berdasarkan tanggal
- Display tim yang aktif pada tanggal tertentu
- Monitoring data verifikasi per tim
- View agregat untuk semua tim
- Loading states dan empty states
- Dark mode support
- Responsive design

## Component Structure

### Script Setup (Composition API)
- **Props**: Menerima data teams dari controller
- **Reactive State**: Form data, loading states, dan active teams
- **API Integration**: Fetch data dinamis dari multiple endpoints
- **Computed Properties**: Filter teams berdasarkan aktivitas
- **Lifecycle Hooks**: Initialize data saat component mount

### Template Structure
- **Layout**: AuthenticatedLayout dengan gradient background
- **Header**: Title dan date picker
- **Content**: Grid layout untuk team tables
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
const today = ref(form.date)        // Reference tanggal hari ini
const activeTeams = ref([])         // Array ID tim yang aktif
const isLoading = ref(true)         // Loading state
```

## Methods

### `fetchActiveTeams()`
**Purpose**: Mengambil data tim yang aktif pada tanggal tertentu

**Flow**:
1. Set loading state ke true
2. Call API `/api/active-teams` dengan parameter date
3. Update activeTeams dengan response data
4. Handle error jika terjadi masalah
5. Set loading state ke false

**API Call**:
```javascript
const response = await axios.get(`/api/active-teams?date=${form.date}`)
```

**Error Handling**:
- Console.error untuk debugging
- Graceful fallback dengan empty array

## Computed Properties

### `filteredTeams`
**Purpose**: Filter workstation berdasarkan tim yang aktif

**Logic**:
```javascript
const filteredTeams = computed(() => {
    return props.teams.filter(team => activeTeams.value.includes(team.id))
})
```

**Returns**: Array workstation yang memiliki aktivitas pada tanggal terpilih

## Watchers

### Date Change Watcher
**Purpose**: Reload data ketika user mengubah tanggal

**Implementation**:
```javascript
watch(() => form.date, () => {
    fetchActiveTeams()
})
```

**Behavior**: Otomatis fetch ulang data tim aktif setiap kali tanggal berubah

## Lifecycle Hooks

### `onMounted()`
**Purpose**: Initialize data saat component pertama kali dimount

**Actions**:
- Call `fetchActiveTeams()` untuk load data awal
- Setup initial state berdasarkan tanggal hari ini

## API Integration

### Active Teams Endpoint
**URL**: `/api/active-teams`
**Method**: GET
**Parameters**: `date` (YYYY-MM-DD format)
**Response**: Array of team IDs yang aktif pada tanggal tersebut

### Child Component API Calls
Component `TableVerifikasiPegawai` melakukan API calls sendiri:
- `/api/team-name/{id}` - Untuk nama tim
- `/api/pendapatan-harian` - Untuk data produksi dan verifikasi

## UI Components Used

### Layout & Structure
- `AuthenticatedLayout`: Base layout dengan navigation
- `Head`: Inertia head component untuk page title

### Form Components
- `InputLabel`: Label untuk date input
- `TextInput`: Date picker input dengan icon
- `Calendar`: Lucide icon untuk date picker

### Data Display
- `TableVerifikasiPegawai`: Component untuk menampilkan data verifikasi per tim
- `LoadingOverlay`: Overlay component untuk loading states

## Styling & Design

### Color Scheme
- **Light Mode**: Blue gradient (blue-50, cyan-50, sky-50)
- **Dark Mode**: Slate gradient (slate-900, slate-800)
- **Accent Colors**: Blue tones untuk interactive elements

### Layout Structure
- **Container**: max-w-[95%] untuk responsive width
- **Grid**: 1 column mobile, 2 columns XL screens
- **Spacing**: Consistent padding dan margins

### Background Effects
- **Grid Pattern**: Subtle grid overlay
- **Gradient Blur**: Decorative blur circles
- **Responsive**: Adapts to different screen sizes

## Component Rendering Logic

### Loading State
```vue
<LoadingOverlay :is-loading="isLoading" />
```

### Empty State
```vue
<div v-if="!isLoading && activeTeams.length === 0" class="text-center py-12">
    <p class="text-lg text-gray-600 dark:text-gray-400">
        Tidak ada data produksi untuk tanggal ini
    </p>
</div>
```

### Data Display
```vue
<template v-else-if="!isLoading">
    <!-- Individual team tables -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-12">
        <template v-for="team in filteredTeams" :key="'team'+team.id">
            <TableVerifikasiPegawai :team="team.id" :date="form.date" />
        </template>
    </div>
    
    <!-- Aggregate table for all teams -->
    <TableVerifikasiPegawai :team="0" :date="form.date" />
</template>
```

## Data Flow

### Initial Load
1. Controller passes workstation data via props
2. Component mounts and calls `fetchActiveTeams()`
3. API returns active team IDs for current date
4. `filteredTeams` computed property filters workstations
5. `TableVerifikasiPegawai` components render for each active team

### Date Change
1. User selects new date in date picker
2. Watcher detects form.date change
3. `fetchActiveTeams()` called with new date
4. Active teams updated, triggering re-render
5. Child components receive new date prop and refresh data

### Child Component Integration
1. Each `TableVerifikasiPegawai` receives team ID and date
2. Child components make their own API calls for detailed data
3. Parent component manages overall loading state
4. Child components handle their own loading and error states

## Performance Considerations

### API Optimization
- Single API call untuk active teams per date change
- Child components handle their own data fetching
- Debouncing tidak diperlukan karena date picker discrete changes

### Rendering Optimization
- `v-for` dengan unique keys untuk efficient re-rendering
- Conditional rendering untuk loading dan empty states
- Computed properties untuk efficient filtering

### Memory Management
- Reactive refs untuk minimal memory footprint
- No memory leaks dari event listeners
- Efficient component cleanup

## Error Handling

### API Errors
- Try-catch blocks untuk semua API calls
- Console logging untuk debugging
- Graceful fallback dengan empty states

### User Experience
- Loading states untuk feedback visual
- Empty states dengan pesan informatif
- Error boundaries untuk component stability

## Accessibility

### Form Controls
- Proper labels untuk date input
- Keyboard navigation support
- Focus management

### Visual Design
- High contrast colors
- Dark mode support
- Responsive design untuk berbagai devices

## Usage Examples

### Basic Usage
```vue
<!-- Dari controller -->
<ProduksiPegawai :teams="workstationData" />
```

### Props Data Example
```javascript
const workstationData = [
    { id: 1, workstation: "Team Production A" },
    { id: 2, workstation: "Team Production B" },
    { id: 3, workstation: "Team Quality Control" }
]
```

### API Response Example
```javascript
// /api/active-teams?date=2024-01-15
[1, 3] // Team IDs yang aktif pada tanggal tersebut
```

## Integration Points

### With Controller
- Receives initial workstation data via Inertia props
- Uses data untuk menampilkan available teams

### With API Endpoints
- `/api/active-teams` untuk filtering tim aktif
- Child components call additional endpoints

### With Child Components
- Passes team ID dan date ke `TableVerifikasiPegawai`
- Manages overall page loading state

## Browser Compatibility

### Modern Features Used
- ES6+ syntax (arrow functions, async/await)
- Vue 3 Composition API
- CSS Grid dan Flexbox
- CSS Custom Properties

### Supported Browsers
- Chrome 88+
- Firefox 85+
- Safari 14+
- Edge 88+

## Development Notes

### Code Organization
- Clean separation of concerns
- Minimal comments dalam code
- Comprehensive external documentation

### Best Practices
- Composition API untuk better logic reuse
- TypeScript untuk type safety
- Reactive state management
- Proper error handling

### Maintenance
- Regular dependency updates
- Performance monitoring
- User feedback integration

## Notes

- Component menggunakan hybrid architecture: SSR untuk initial load, SPA untuk dynamic updates
- Date filtering adalah core functionality yang mempengaruhi semua child components
- Loading states dikelola secara hierarchical (parent untuk page, child untuk individual tables)
- Responsive design menggunakan Tailwind CSS utility classes
- Dark mode support built-in dengan proper color schemes
- Component ini adalah bagian dari monitoring module yang lebih besar
- Integration dengan authentication system melalui AuthenticatedLayout
- Menggunakan Inertia.js untuk seamless navigation tanpa full page reload

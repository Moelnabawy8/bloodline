@extends('layouts.app')

@section('title', 'Hospitals Map')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
.map-wrap { max-width: 1200px; margin: 25px auto; padding: 0 20px; }
.map-header { background: var(--card-bg); padding: 22px; border-radius: 14px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); margin-bottom: 18px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; }
.map-header h2 { color: var(--main-red); font-weight: bold; margin: 0; }
.legend { display: flex; gap: 14px; font-size: 13px; color: #666; align-items: center; }
.legend .dot { width: 12px; height: 12px; border-radius: 50%; display: inline-block; margin-right: 5px; }
#map { width: 100%; height: 600px; border-radius: 14px; box-shadow: 0 6px 18px rgba(0,0,0,0.08); z-index: 1; }
.locate-btn { background: var(--main-red); color: white; border: none; padding: 9px 18px; border-radius: 20px; cursor: pointer; font-weight: bold; font-size: 13px; }
.locate-btn:hover { background: var(--main-red-dark); }
.empty-state { background: #fff8f8; border: 2px dashed #ddd; padding: 40px; text-align: center; border-radius: 14px; color: #888; }
</style>
@endpush

@section('content')
<div class="map-wrap">
    <div class="map-header">
        <div>
            <h2><i class="fas fa-map-marked-alt"></i> Hospitals & Blood Banks</h2>
            <small style="color:#888;">Click any marker for hospital details.</small>
        </div>
        <div class="legend">
            <span><span class="dot" style="background:#dc3545;"></span>Has active requests</span>
            <span><span class="dot" style="background:#28a745;"></span>No urgent cases</span>
            <button id="locateMe" class="locate-btn"><i class="fas fa-location-arrow"></i> Find nearest</button>
        </div>
    </div>

    @if ($markers->isEmpty())
        <div class="empty-state">
            <i class="fas fa-map" style="font-size:50px;color:#ddd;"></i>
            <h4 style="margin-top:14px;">No hospitals on the map yet</h4>
            <p>Hospitals with registered coordinates will appear here.</p>
        </div>
    @else
        <div id="map"></div>
    @endif
</div>
@endsection

@push('scripts')
@if ($markers->isNotEmpty())
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
(function () {
    const hospitals = @json($markers);

    // Center on Egypt by default
    const map = L.map('map').setView([26.8206, 30.8025], 6);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    const redIcon = L.divIcon({
        className: 'custom-marker',
        html: '<div style="background:#dc3545;width:24px;height:24px;border-radius:50%;border:3px solid white;box-shadow:0 2px 6px rgba(0,0,0,0.3);"></div>',
        iconSize: [24, 24], iconAnchor: [12, 12]
    });
    const greenIcon = L.divIcon({
        className: 'custom-marker',
        html: '<div style="background:#28a745;width:24px;height:24px;border-radius:50%;border:3px solid white;box-shadow:0 2px 6px rgba(0,0,0,0.3);"></div>',
        iconSize: [24, 24], iconAnchor: [12, 12]
    });

    const bounds = [];
    const markerLayer = L.layerGroup().addTo(map);

    hospitals.forEach(h => {
        const icon = h.active > 0 ? redIcon : greenIcon;
        const popup = `
            <strong>${h.name}</strong><br>
            <small style="color:#666;">${h.district}</small><br>
            ${h.active > 0
                ? `<span style="color:#dc3545;font-weight:bold;">⚠ ${h.active} active request(s)</span>`
                : '<span style="color:#28a745;">No urgent cases</span>'}
            <br><a href="/requests?hospital=${encodeURIComponent(h.name)}" style="color:var(--main-red);font-weight:bold;">View requests →</a>
        `;
        L.marker([h.lat, h.lng], { icon }).addTo(markerLayer).bindPopup(popup);
        bounds.push([h.lat, h.lng]);
    });

    if (bounds.length > 1) {
        map.fitBounds(bounds, { padding: [40, 40] });
    } else if (bounds.length === 1) {
        map.setView(bounds[0], 12);
    }

    document.getElementById('locateMe')?.addEventListener('click', () => {
        if (!navigator.geolocation) { alert('Geolocation not supported.'); return; }
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                const me = [pos.coords.latitude, pos.coords.longitude];
                L.circleMarker(me, { radius: 10, color: '#0d6efd', fillColor: '#0d6efd', fillOpacity: 0.6 })
                    .addTo(map).bindPopup('You are here').openPopup();
                map.setView(me, 11);
            },
            () => alert('Unable to retrieve your location.')
        );
    });
})();
</script>
@endif
@endpush

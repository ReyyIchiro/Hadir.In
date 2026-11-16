<?php

namespace App\Services;

use App\Models\Location;
use App\Models\Tenant;
use App\Support\Flags;
use Illuminate\Support\Collection;

class GeofenceService
{
    public const EARTH_RADIUS_METERS = 6371000;

    /** Batas akurasi absolut: di atas ini koordinat dianggap tidak dapat dipercaya. */
    public const MAKS_AKURASI_METER = 500.0;

    public function distance(float $lat1, float $lng1, float $lat2, float $lng2): int
    {
        $latFrom = deg2rad($lat1);
        $latTo = deg2rad($lat2);
        $latDelta = deg2rad($lat2 - $lat1);
        $lngDelta = deg2rad($lng2 - $lng1);

        $a = sin($latDelta / 2) ** 2
            + cos($latFrom) * cos($latTo) * sin($lngDelta / 2) ** 2;

        return (int) floor(self::EARTH_RADIUS_METERS * 2 * atan2(sqrt($a), sqrt(1 - $a)));
    }

    /**
     * @param  Collection<int, Location>  $locations
     * @return array{location: ?Location, distance: ?int, inside: bool, within_buffer: bool}
     */
    public function evaluate(Collection $locations, float $latitude, float $longitude, int $bufferMeters): array
    {
        $nearest = null;
        $nearestDistance = null;

        foreach ($locations as $location) {
            $distance = $this->distance(
                $latitude,
                $longitude,
                (float) $location->latitude,
                (float) $location->longitude,
            );

            if ($nearestDistance === null || $distance < $nearestDistance) {
                $nearest = $location;
                $nearestDistance = $distance;
            }
        }

        if ($nearest === null || $nearestDistance === null) {
            return ['location' => null, 'distance' => null, 'inside' => false, 'within_buffer' => false];
        }

        if ($nearestDistance <= $nearest->radius_meters) {
            return [
                'location' => $nearest,
                'distance' => $nearestDistance,
                'inside' => true,
                'within_buffer' => false,
            ];
        }

        $withinBuffer = $nearestDistance <= $nearest->radius_meters + $bufferMeters;

        return [
            'location' => $withinBuffer ? $nearest : null,
            'distance' => $nearestDistance,
            'inside' => false,
            'within_buffer' => $withinBuffer,
        ];
    }

    /**
     * @return array{accepted: bool, flags: array<int, string>, reason: ?string, location: ?Location, distance: ?int}
     */
    public function validate(Tenant $tenant, ?float $latitude, ?float $longitude, ?float $accuracy): array
    {
        $settings = $tenant->settings;
        $flags = [];
        $locations = $tenant->locations()->where('is_active', true)->get();

        if ($accuracy !== null && $accuracy > $settings->low_accuracy_meters) {
            $flags[] = Flags::AKURASI_RENDAH;
        }

        // Gerbang keras sebelum cek lain: koordinat yang tidak dapat dipercaya
        // tidak boleh lolos walau kebetulan masuk radius.
        if ($accuracy !== null && $accuracy > self::MAKS_AKURASI_METER) {
            return [
                'accepted' => false,
                'flags' => $flags,
                'reason' => 'akurasi_tidak_dapat_dipercaya',
                'location' => null,
                'distance' => null,
            ];
        }

        if ($locations->isEmpty()) {
            if ($settings->require_location && $latitude === null) {
                return [
                    'accepted' => false,
                    'flags' => $flags,
                    'reason' => 'lokasi_tidak_tersedia',
                    'location' => null,
                    'distance' => null,
                ];
            }

            $flags[] = Flags::TANPA_LOKASI;

            return [
                'accepted' => true,
                'flags' => $flags,
                'reason' => null,
                'location' => null,
                'distance' => null,
            ];
        }

        if ($latitude === null || $longitude === null) {
            if ($settings->require_location) {
                return [
                    'accepted' => false,
                    'flags' => $flags,
                    'reason' => 'lokasi_tidak_tersedia',
                    'location' => null,
                    'distance' => null,
                ];
            }

            $flags[] = Flags::TANPA_LOKASI;

            return [
                'accepted' => true,
                'flags' => $flags,
                'reason' => null,
                'location' => null,
                'distance' => null,
            ];
        }

        $result = $this->evaluate($locations, $latitude, $longitude, $settings->geofence_buffer_meters);

        if ($result['inside']) {
            return [
                'accepted' => true,
                'flags' => $flags,
                'reason' => null,
                'location' => $result['location'],
                'distance' => $result['distance'],
            ];
        }

        if ($result['within_buffer']) {
            $flags[] = Flags::DI_LUAR_RADIUS;

            return [
                'accepted' => true,
                'flags' => $flags,
                'reason' => null,
                'location' => $result['location'],
                'distance' => $result['distance'],
            ];
        }

        return [
            'accepted' => false,
            'flags' => $flags,
            'reason' => 'di_luar_radius',
            'location' => null,
            'distance' => $result['distance'],
        ];
    }
}

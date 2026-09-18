# DUET Vehicle Tracking System

A web-based real-time GPS tracking and fleet management system for university
transport, built for the Software Engineering laboratory (DUET, Gazipur).

A driver starts a trip from a phone, the browser reports its GPS position on a
fixed interval (10 minutes by default), and passengers see the bus move on a
live map with the estimated arrival time at each stoppage. Administrators manage the fleet, routes,
stoppages, schedules and notices, and review how the fleet was used.

## Stack

PHP 8 + MySQL (XAMPP), Bootstrap 5, Leaflet with OpenStreetMap tiles.
No build step and no API key: open the folder in `htdocs` and it runs.

## Setup

1. Copy the project to `C:\xampp\htdocs\Lab` and start Apache and MySQL.
2. Create the database and tables:

   ```
   mysql -u root < database/schema.sql
   ```

3. Optional, for a ready-to-show system:

   ```
   mysql -u root duet_vts < database/seed_service.sql
   ```

   That creates the three routes with their road geometry, ten drivers with a
   vehicle each, and the daily timetable.

4. Open <http://localhost/Lab>.

`config/db.php` holds the database credentials, the application timezone
(`Asia/Dhaka`, also pushed to the MySQL session so PHP and the database agree
on the clock) and the two tracking intervals:

| Setting | Default | Meaning |
|---|---|---|
| `LOCATION_UPDATE_SECONDS` | 600 (10 min) | how often the driver's phone reports its position |
| `MAP_REFRESH_SECONDS` | 30 | how often a map asks the server for new positions |

Lower `LOCATION_UPDATE_SECONDS` for a live demonstration (30-60 s) and raise it
to save the driver's mobile data. The map never shows a position newer than the
reporting interval, and the trip distance is measured between reported points,
so a long interval also makes the recorded distance coarser.

## Roles

| Role | Can do |
|---|---|
| Admin | Vehicles, drivers, routes with stoppages, schedules, notices, trip history, analytics, CSV export |
| Driver | Start and end a trip, share live GPS, see own trip history |
| Student / Teacher | Live map, route with stoppages and ETA, schedules, notices |

`seed_service.sql` creates every account a fresh install needs, all with the
password `123456`:

| Role | Accounts |
|---|---|
| Admin | `admin1@gmail.com` |
| Driver | `driver1@gmail.com` … `driver10@gmail.com` |
| Student | `student1@gmail.com` … `student10@gmail.com` |
| Teacher | `teacher1@gmail.com` … `teacher10@gmail.com` |

The registration form deliberately creates passengers only, so the seeded
administrator is the way into the admin panel on a new database. Change its
password before using the system for real.

## Service timetable

The service runs Saturday to Thursday; **Friday is off**, which the timetable
stores as the `SatThu` day value rather than one row per day. Students and
teachers ride separate buses on both services.

| Departure | Student bus | Teacher bus | Route |
|---|---|---|---|
| 7:40 AM, 1:00 PM | CS-Bus-1 | CT-Bus-1 | 1st campus → 2nd campus |
| 7:40 AM, 1:00 PM | CS-Bus-2 | CT-Bus-2 | 2nd campus → 1st campus |
| 10:15 AM, 4:40 PM | CS-Bus-2 | CT-Bus-2 | 1st campus → 2nd campus |
| 10:15 AM, 4:20 PM | CS-Bus-1 | CT-Bus-1 | 2nd campus → 1st campus |
| 5:00 PM | S-Bus-1, S-Bus-2 | T-Bus-1, T-Bus-2 | DUET campus → Nilkhet, Dhaka |
| 9:50 PM | S-Bus-1, S-Bus-2 | T-Bus-1, T-Bus-2 | Nilkhet, Dhaka → DUET campus |

Each shuttle pair alternates direction, so a bus always starts from where its
previous trip ended: CS-Bus-1 runs out at 7:40 and back at 10:15, while
CS-Bus-2 does the opposite. The Dhaka buses take passengers to Nilkhet in the
evening and bring them back at night — about 96 km and two hours of driving
per bus per day. GEN-Micro-1 and AMB-1 have no fixed departure; they run on
call.

Eight buses in service and ten drivers: one per bus, plus the microbus and the
ambulance. The heaviest daily load is two Dhaka runs or four short shuttle
trips, so no driver spends more than about two hours behind the wheel.

A driver signing in sees only their own departures for the day, with the one
that is due highlighted; starting it records which scheduled departure the trip
belongs to, so the timetable shows Completed - and how many minutes late - once
the trip ends.

## Testing

`tools/run_tests.sh` signs in as each role and checks every page, the JSON
APIs, role-based access control and the GPS endpoint's rejection of bad input:

```
bash tools/run_tests.sh
```

## Layout

```
admin/      fleet, route, schedule, notice management, analytics, CSV export
driver/     trip start/end, GPS updates, own trip history
student/    passenger dashboard, live tracking page
teacher/    passenger dashboard with route and ETA
api/        vehicle_locations.php (live feed), vehicle_track.php (one trip's path)
includes/   auth guards, shared helpers (distance, ETA, delay, deviation)
assets/     stylesheet and the Leaflet map script
database/   schema and seed data
tools/      trip simulator, tests, load test, backup and cleanup
docs/       the lab report, the SRS, the presentation and what builds them
```

## Notes

- Routes store their road geometry in `routes.path_json`, so the map draws the
  line along the streets instead of joining stoppages with straight segments.
- Trips store `schedule_id` and `delay_minutes`, which is what the punctuality
  figures on the analytics page are built from.
- The Geolocation API only returns coordinates on `localhost` or over HTTPS,
  so a real deployment needs a TLS certificate.

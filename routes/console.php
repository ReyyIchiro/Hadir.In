<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('attendance:finalize')->hourly()->withoutOverlapping();
Schedule::command('selfies:purge')->dailyAt('02:00')->withoutOverlapping();
Schedule::command('model:prune')->dailyAt('03:00')->withoutOverlapping();

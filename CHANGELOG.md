# Changelog

All notable changes to `filament-tour` will be documented in this file.

## 3.3.1 - 2025-03-20

Support Route Name

## v3.1.1 - 2024-11-05

### What's Changed

* fixes bug #23 - with wrong routes for unauthenticated multinenancy users by @OccTherapist in https://github.com/JibayMcs/filament-tour/pull/24

**Full Changelog**: https://github.com/JibayMcs/filament-tour/compare/v3.1.0.9...v3.1.1

## v3.1.0.9 - 2024-09-11

### What's Changed

* Fixing Typo in my last commit by @OccTherapist in https://github.com/JibayMcs/filament-tour/pull/22

**Full Changelog**: https://github.com/JibayMcs/filament-tour/compare/v3.1.0.8...v3.1.0.9

## v3.1.0.8 - 2024-09-10

### What's Changed

* add german translations by @OccTherapist in https://github.com/JibayMcs/filament-tour/pull/21

### New Contributors

* @OccTherapist made their first contribution in https://github.com/JibayMcs/filament-tour/pull/21

**Full Changelog**: https://github.com/JibayMcs/filament-tour/compare/v3.1.0.7...v3.1.0.8

## v3.1.0.7 - 2024-08-06

### What's Changed

* suitable for what json format works | Update Step.php by @MrPowerUp82 in https://github.com/JibayMcs/filament-tour/pull/19

### New Contributors

* @MrPowerUp82 made their first contribution in https://github.com/JibayMcs/filament-tour/pull/19

**Full Changelog**: https://github.com/JibayMcs/filament-tour/compare/v3.1.0.6...v3.1.0.7

## v3.1.0.6 - 2024-08-06

### Fixes

- Fixed THE major issue regarding to the route system, basically caused by unauthentified user
- Fixed opening highlights on click the highlight button
- Fixed event to open a tour, ex: used in a custom Action:
    ```php
    protected function getHeaderActions(): array
  {
  return [
  Action::make('Tour')->dispatch('filament-tour::open-tour', ['tour_dashboard']),
  ];
  }
  
  
  
  
  
  
    ```

```

**Full Changelog**: https://github.com/JibayMcs/filament-tour/compare/v3.1.0.5...3.x






```
## v3.1.0.5 - 2024-05-10

### What's Changed

* chore(deps): upd illuminate/contracts to laravel 11 by @pepperfm in https://github.com/JibayMcs/filament-tour/pull/17

### New Contributors

* @pepperfm made their first contribution in https://github.com/JibayMcs/filament-tour/pull/17

**Full Changelog**: https://github.com/JibayMcs/filament-tour/compare/v3.1.0.4...v3.1.0.5

## v3.1.0.4 - 2024-04-17

### What's Changed

* Fixed translation by @theHocineSaad in https://github.com/JibayMcs/filament-tour/pull/10
* Fix the  filament-tour::open-tour  and  filament-tour::open-highlight problems  by @wallacemaxters in https://github.com/JibayMcs/filament-tour/pull/14

### New Contributors

* @theHocineSaad made their first contribution in https://github.com/JibayMcs/filament-tour/pull/10
* @wallacemaxters made their first contribution in https://github.com/JibayMcs/filament-tour/pull/14

**Full Changelog**: https://github.com/JibayMcs/filament-tour/compare/v3.1.0.3...v3.1.0.4

## v3.1.0.3 - 2023-09-20

### What's Changed

- add ar translations by @aymanalareqi in https://github.com/JibayMcs/filament-tour/pull/4

### New Contributors

- @aymanalareqi made their first contribution in #4

**Full Changelog**: https://github.com/JibayMcs/filament-tour/compare/v3.1.0.2...v3.1.0.3

## What's new ? - 2023-09-14

- Fixed error on parsing URL parameters from the tour routing system

**Full Changelog**: https://github.com/JibayMcs/filament-tour/compare/v3.1.0.1...v3.1.0.2

## The JSON Update - 2023-09-06

### The JSON Update <sup>ᴠ3.1.0.1</sup>

If creating your guided tours in PHP bores you or takes up too much space, play with JSON!

You can now load your tours directly using a JSON file from a URL or your Storage!

- Finished setup for multiple tours registration, now "goto" a next tour on finished the first one

## v3.1.0.0 - 2023-09-05

### The First Release !

#### Development Tool :eyes:

[Check it here !](https://github.com/JibayMcs/filament-tour/blob/3.x/README.md#development-tool)

#### Tour

- Added to make all child steps uncloseable
  
  - `function uncloseable(bool|Closure $uncloseable = true)`
  
- Added to disable all steps events
  
  - `function disableEvents(bool|Closure $disableEvents = true)`
  
- Added to ignore routes check to launch Tour
  
  - `function ignoreRoutes(bool|Closure $ignoreRoutes = true)`
  

## 1.0.0 - 202X-XX-XX

- initial release

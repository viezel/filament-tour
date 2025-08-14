# Bring the power of DriverJs to your Filament panels

With the power of [DriverJS](https://driverjs.com) bring to your users an elegant way to discover your panels. Original work done by [JibayMcs](https://github.com/JibayMcs).

## Installation

You can install this filament plugin via composer:

For Filament v3.x

```bash
composer require viezel/filament-tour:"^3.0"
```

For Filament v2.x

```bash
composer require jibaymcs/filament-tour:"^2.0"
```

You can publish the config/views/migrations file with:

```bash 
php artisan vendor:publish --tag="filament-tour-config"
php artisan vendor:publish --tag="filament-tour-views"
php artisan vendor:publish --tag="filament-tour-migrations"
```

This is the contents of the published config file:

```php
 return [    
    'only_visible_once' => true,
    'enable_css_selector' => false,
    'tour_prefix_id' => 'tour_',
    'highlight_prefix_id' => 'highlight_',
];
```

## Usage

```php
use Viezel\FilamentTour\FilamentTourPlugin;

public function panel(Panel $panel) {
	return $panel->default()
		->[...]
		->plugins([ FilamentTourPlugin::make() ]);
}  
```

You can also enable or disable the check on the local storage if the current user have already completed the tour.

```php
// default  : true  
FilamentTourPlugin::make()->onlyVisibleOnce(false)  
```

# Tour Autostart

By default, the tours will be started automatically when the page is loaded.

You can disable this behaviour globally by setting `auto_start_tours` to false in `config/filament-tour.php`.

```php
<?php

return [
    'only_visible_once' => true,
    'enable_css_selector' => false,
    'tour_prefix_id' => 'tour_',
    'highlight_prefix_id' => 'highlight_',
    'auto_start_tours' => false,
];
```

Alternatively, you can disable the auto start for a specific panel by calling the `autoStart` method with a parameter of `false`.

```php
use Viezel\FilamentTour\FilamentTourPlugin;

public function panel(Panel $panel) {
	return $panel->default()
		->[...]
		->plugins([ FilamentTourPlugin::make()->autoStart(false) ]);
}  
```
## Tour History Storage

By default, the Tour plugin uses local storage, which works great for both **authenticated users and guest users** 
as it will store a tour completion locally in the users' browser. This options gets you started quickly. 

However, sometimes you want to opt-in for at more robust solution to ensure that users does not experience the tour twice. 
In this situation you might want to use the database option. It will kept record of completed tours in the database.
This option only works for **authenticated users**.

```php
use Viezel\FilamentTour\FilamentTourPlugin;
use Viezel\FilamentTour\Tour\Enums\TourHistoryType;

public function panel(Panel $panel) {
	return $panel->default()
		->[...]
		->plugins([ 
		    FilamentTourPlugin::make() 
		        ->historyType(TourHistoryType::Database),  
        ]);
} 
```

Remember to publish the migrations in order to use the database option.

```bash
php artisan vendor:publish --tag="filament-tour-migrations"
```


# Start a tour !

Let's follow this example to add a tour to your dashboard page.

If you don't already have a customized dashboard, please refer to the following tutorial: [FIlamentPHP - Dashboard - Customizing the dashboard page](https://filamentphp.com/docs/3.x/panels/dashboard#customizing-the-dashboard-page)

## Use the correct trait to registers your tours !

```php
<?php  
namespace App\Filament\Pages;  
  
use Viezel\FilamentTour\Tour\HasTour;
  
class Dashboard extends FilamentDashboard 
{
    use HasTour;
    // ...  

	public function tours(): array
	{    
		return []; 
	}
}
```
___

### Create a simple tour !

```php
use Viezel\FilamentTour\Tour\Step;
use Viezel\FilamentTour\Tour\Tour;

public function tours(): array 
{
    return [
       Tour::make('dashboard')
           ->steps(        
               Step::make()
                   ->title("Welcome to your Dashboard !")
                   ->description(view('tutorial.dashboard.introduction')),
    
               Step::make('.fi-avatar')
                   ->title('Woaw ! Here is your avatar !')
                   ->description('You look nice !')
                   ->icon('heroicon-o-user-circle')
                   ->iconColor('primary')
           ),
    ];
}
```

### Displaying your tour !

In order to display your tour, its important to remember to pass in the route for the given path you'd like to have the
tour show up on - if you'd like to render the tour on the main admin panels dashboard, set the route to:

```php
->route('/admin')
```

or use a route name

```php
->routeName('filament.admin.pages.dashboard')
```

Alternatively, you may want to show tours based on more complex logic, for example if a user hasn't created a specific
type of content.  In order to show a tour you will need to create an event listener in your livewire component.
Continuing with the Dashboard example, let's show a tour only if the user hasn't created a new Post yet.  We can write
a query to see if the user has no posts, and if so, fire an `open-tour` event to show the Tour:

```php
use App\Models\Post;
use Illuminate\Support\Facades\Config;
use Livewire\Attributes\On;

/**
 * Renders the first tour.
 *
 * @param boolean $only_visible_once Whether the tour should only be visible once.
 * @param array   $tours             Tours to render.
 * @param array   $highlights        Highlights to render.
 *
 * @return void
 */
#[On('filament-tour::loaded-elements')]
public function renderPostTour(bool $only_visible_once, array $tours, array $highlights): void
{
    // If there are posts, don't show this tour
    if (Post::count() > 0) {
        return;
    }

    // Get the tour prefix value
    $prefix = Config::get('filament-tour.tour_prefix_id');

    // Remove the prefix
    $firstTourID = substr($tours[0]['id'], strlen($prefix));

    // Dispatch the event to open the tour
    $this->dispatch('filament-tour::open-tour', $firstTourID);
}
```

### Use Events for your custom business logic

Sometimes you want to run business logic in when certain lifecycle event like completed and dismissed occurs. Here is how to: 

```php
use Viezel\FilamentTour\Tour\Step;
use Viezel\FilamentTour\Tour\Tour;
use Livewire\Attributes\On;

#[On('dashboard-tour-completed')]
public function completed($params): void
{
    // your logic here
}

#[On('dashboard-tour-dismissed')]
public function dismissed($params): void
{
    // your logic here
}

public function tours(): array 
{
    return [
       Tour::make('dashboard')
           ->routeName('filament.admin.pages.dashboard')
           ->alwaysShow(false)
           ->dispatchOnComplete('dashboard-tour-completed', [
                'foo' => 'bar',
            ])
            ->dispatchOnDismiss('dashboard-tour-dismissed', [
                'foo' => 'bar',
            ])
           ->steps(        
               Step::make()
                   ->title("Welcome to your Dashboard !")
                   ->description(view('tutorial.dashboard.introduction')),
               Step::make('.fi-avatar')
                   ->title('Woaw ! Here is your avatar !')
                   ->description('You look nice !'),
           ),
    ];
}
```



You can also bring up tours for users when they click on a button. See more in the (Event)[#events] section.

### Create a JSON tour !

#### - From a direct URL
```php
use Viezel\FilamentTour\Tour\Tour;

public function tours(): array {
    return [
       Tour::make(url: "https://example.com/tour-dashboard.json")
    ];
}
```

#### - From your Storage

```php
use Viezel\FilamentTour\Tour\Tour;
use Illuminate\Support\Facades\Storage;

public function tours(): array {
    return [
       Tour::make(json: Storage::disk('local')->get("TourDashboard.json"))
    ];
}
```

> [!IMPORTANT]  
> Using `Tour::make(url: "")` or `Tour::make(json: "")` is the same thing, so don't worry about the name of your parameter if you've got the wrong type.<br>
> BUT<br>
> If you use `Tour::make('my-tour')` it's equal to `Tour::make(id: 'my-tour')`
> And here you need to construct all your steps. No JSON reading here.

<details>
	<summary><b>JSON example file</b> (click to expand)</summary>
    
```json
{
    "id": "dashboard",
    "route": "/admin/test-team",
    "colors": [
        "",
        ""
    ],
    "alwaysShow": true,
    "visible": true,
    "showProgress": true,
    "uncloseable": true,
    "ignoreRoutes": false,
    "disableEvents": true,
    "nextButtonLabel": "Next",
    "previousButtonLabel": "Previous",
    "doneButtonLabel": "Done",
    "steps": [
        {
            "title": "Woaw ! First Step !",
            "description": "Yeah ! And I'm from a json file !",
            "uncloseable": false,
            "events": {
                "clickOnNext": "body",
                "notifyOnNext": {
                    "title": "Hello World !",
                    "body": "Woaw ! I'm from a Json file !",
                    "color": "success"
                },
                "redirectOnNext": {
                    "url": "https://filamentphp.com",
                    "newTab": true
                },
                "dispatchOnNext": [
                    "open-modal",
                    {
                        "id": "edit-user"
                    }
                ]
            }
        },
        {
            "title": "An other one !",
            "description": "Yeah ! And I'm from the same json file !",
            "uncloseable": false,
            "events": {
                "clickOnNext": "body",
                "notifyOnNext": {
                    "title": "Hello World !",
                    "body": "Woaw ! I'm from a Json file !",
                    "color": "success"
                },
                "redirectOnNext": {
                    "url": "https://filamentphp.com",
                    "newTab": true
                },
                "dispatchOnNext": [
                    "open-modal",
                    {
                        "id": "edit-user"
                    }
                ]
            }
        }
    ]
}
```
</details>

### Custom Theming

In order to create a custom theme for the tour, please set the `popoverClass` property. 

```php
Tour::make('dashboard')
    ->popoverClass('my-custom-popover-class')
```

More info in the [theme section of Driver.js](https://driverjs.com/docs/theming)

# Tour.php

### Tour methods reference

```php
use Viezel\FilamentTour\Tour\Tour;

// Instantiate a tour, and provide an id, to trigger it later
Tour::make(string $id)

// Since 3.1.0.1, JSON Support update
Tour::make(... $params)

    // Define a custom url to trigger your tour 
    ->route(string $route)
    
    // or use a route name to trigger your tour 
    ->routeName(string $routeName)
    
    // Register the steps of your tour
    ->steps(Step ...$steps)
    
    // Define a color of your highlight overlay for the dark and light theme of your filament panel
    ->colors(string $light, string $dark)
    
    // Set the tour as always visible, even is already viewed by the user.
    ->alwaysShow(bool|Closure $alwaysShow = true)
    
    // Show or hide the progress indicator
    ->showProgress(bool $showProgress = true)
    
    // Override the default progress text: "{{current}} of {{total}}"
    ->progressText(string $progressText)
    
    // Set a custom class for custom theming
    ->popoverClass(string $popoverClass)
    
    // Set the tour visible or not
    ->visible(bool|Closure $visible = true)
    
    // Set the 'Next' button label
    ->nextButtonLabel(string $label)
    
    // Set the 'Previous' button label
    ->previousButtonLabel(string $label)
    
    // Set the 'Done' button label
    ->doneButtonLabel(string $label)
    
    // Set the whole steps of the tour as uncloseable
    ->uncloseable(bool|Closure $uncloseable = true)
    
    // Disable all tour steps events
    ->disableEvents(bool|Closure $disableEvents = true)
    
    // Dispatch an event like `$dispatch()` when the user completes the tour
    ->dispatchOnComplete(string $name, ...$args)
    
    // Dispatch an event like `$dispatch()` when the user dismisses the tour
    ->dispatchOnDismiss(string $name, ...$args)
    
    // Should tour be marked as completed if user dismisses it
    ->shouldCompleteOnDismiss(bool $shouldCompleteOnDismiss = true)
    
    // Should tour be marked as completed if user dismisses it
    // Maybe useless, but who knows ?
    ->ignoreRoutes(bool|Closure $ignoreRoutes = true)
```

# Step.php

### Step methods reference

```php
use Viezel\FilamentTour\Tour\Step;

// If no element provided, the step act like a modal
Step::make(string $element = null)

    // Define the title of your step
    // Mandatory
    ->title(string|Closure $title)
    
    // Define the description of your step
    // Also accept HTML
    // Mandatory
    ->description(string|Closure|HtmlString|View $description)
    
    // Define an icon next to your step title
    ->icon(string $icon)
    
    // Define the color of the title icon
    ->iconColor(string $color)
    
    // Step your step closeable or not
    // Default: true
    ->uncloseable(bool|Closure $uncloseable = true)
    
    // Simulate a click on a CSS selected element when you press the next button
    ->clickOnNext(string|Closure $selector)
    
    // Send a notification when you press the next button
    ->notifyOnNext(Notification $notification)
    
    // Redirect you to a custom url or a route() when you press the next button
    ->redirectOnNext(string $url, bool $newTab = false)
    
    // Dispatch an event like `$dispatch()` when you press the next button
    ->dispatchOnNext(string $name, ...$args)
```

# Highlights

Same as tour, use the `HasHighlight` trait. 

```php
<?php

namespace App\Filament\Pages;  
  
use Viezel\FilamentTour\Highlight\HasHighlight;  
  
class Dashboard extends FilamentDashboard {

    use HasHighlight;
  
    public function highlights(): array 
    {    
	    return []; 
    }
}
```

- Create a simple highlight element !

```php
use Viezel\FilamentTour\Highlight\Highlight;

public function highlights(): array 
{
    return [
	 
        Highlight::make('.fi-header-heading')
            ->element('.fi-header-heading')
            ->title('Whoaw ! You highlighted the title of the page !')
            ->description('"Dashboard"'),
	
        Highlight::make('.fi-avatar')
            ->element('.fi-avatar')
            ->title("Pssst ! That's your avatar")
            ->icon('heroicon-o-user-circle')
            ->iconColor('primary'), 	
    ];
}
```

___

# Highlight.php

### Highlight methods reference

```php
use Viezel\FilamentTour\Highlight\Highlight;

// Instantiate a highlight with a CSS select of the element where the icon button is next to
Highlight::make(string $parent)

    // Define the element to be highlighted
    ->element(string $element)

    // Set the title of your highlight
    ->title(string|Closure $title)

    // Set the description of your highlight
    ->description(string|Closure|HtmlString|View $description)

    // Define a custom icon for your highlight button
    // Default: heroicon-m-question-mark-circle
    ->icon(string $icon)

    // Define the color of the highlight icon button
    // Default: gray
    ->iconColor(string $color)

    // Define a color of your highlight overlay for the dark and light theme of your filament panel
    ->colors(string $light, string $dark)

    // Set the position of your icon button around the parent
    // Default: top-left
    // Available: top-left, top-right, bottom-left, bottom-right
    ->position(string $position)
```

___

# Events

### Available events:

- `filament-tour::open-highlight` **string** id  
  Open a specific highlight by its id.
  <br>
  <br>
- `filament-tour::open-tour` **string** id  
  Open a specific tour by its id.
  <br>
  <br>
- `filament-tour::tour-completed` **string** id
  Occurs when tour has completed
  <br>
  <br>
- `filament-tour::tour-dismisses` **string** id
  Occurs when user closes the tour before its completed
___

Filament Tour, dispatch some event to show tours and highlights.
So you can trigger them from your own code.

Basically, if you want a custom button to trigger a tour or a highlight, you can do something like this:

```html
// ======== Highlights
// AlpineJS
<button x-on:click="Livewire.dispatch('filament-tour::open-highlight', 'title')">Show title highlight</button>

// Livewire
<button wire:click="$dispatch('filament-tour::open-highlight', 'title')">Show title highlight</button>

// ======== Tours
//AlpineJS
<button x-on:click="Livewire.dispatch('filament-tour::open-tour', 'title')">Show Dashboard tour</button>

// Livewire
<button wire:click="$dispatch('filament-tour::open-tour', 'dashboard')">Show Dashboard tour</button>
```

```php
// Filament Action Button
Action::make('open_tour_btn')
    ->label('Show Dashboard tour')
    ->icon('heroicon-o-play-circle')
    ->dispatch('filament-tour::open-tour', ['dashboard']),
```


# Development Tool

> [!IMPORTANT]  
> This tool is always disabled in production mode. `APP_ENV=production`

Filament Tour embed a simple tool to help you to develop your tours and highlights.

Let me show you how to use it !

### Enable the tool

To enable the tool, simply use `FilamentTourPlugin::make()->enableCssSelector()` in your plugin declaration.

### Keyboard shortcuts

<kbd>**Ctrl**</kbd>|<kbd>**Cmd**</kbd> + <kbd>**Space**</kbd> To open the tool.
<br>
<br>
<kbd>**Escape**</kbd> To exit the tool.
<br>
<br>
<kbd>**Ctrl**</kbd>|<kbd>**Cmd**</kbd> + <kbd>**C**</kbd> To copy the CSS Selector of the highlighted element.

[CSS Selector Tool Utilisation Preview](https://github.com/Viezel/filament-tour/assets/7621593/162db2a3-1f46-4493-ae0d-cffcb2f00462)

# Extra Resources

### DriverJS

- [DriverJS Website](https://driverjs.com)
- [DriverJS GitHub](https://github.com/kamranahmedse/driver.js) (Give some 🩵 to the author !)

The core of this plugin !  
Don't hesitate to check the documentation to learn more about the possibilities of this plugin.  
_I don't implemented all the features of DriverJS, at this time, but I'm working on it !_

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [JibayMcs](https://github.com/JibayMcs)
- [Viezel](https://gituhb.com/viezel)
- [DriverJS](https://driverjs.com)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.


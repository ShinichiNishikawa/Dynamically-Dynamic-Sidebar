# Dynamically Dynamic Sidebar

Create unlimited custom sidebar/widget areas and switch existing areas depending on post, page, custom-post-type post, categories, tags and custom taxonomy terms. You can do this without touching your theme.

## Requirements

### WordPress version 4.4 or above.

This plugin utilizes term meta api and so it works only with WordPress 4.4 and above.

### Proper use of `is_active_sidebar()`

Your theme needs to use `is_active_sidebar()` when calling `dynamic_sidebar()` function.

#### Automatic widget area switch will work.

```
if ( is_active_sidebar( 'sidebar-1' ) ) {
	dynamic_sidebar( 'sidebar-1' );
}
```

#### This doesn't work.

`
dynamic_sidebar( 'sidebar-1' );
`

## How to just print your area instead of switching existing area

If you only want to output your custom sidebar, put this code on where you want to display the sidebar in your theme.

`do_action( 'dynamically_dynamic_sidebar' );`


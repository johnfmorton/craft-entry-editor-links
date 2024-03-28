# Entry Editor Link Plugin

_Entry Editor Link_ is a plugin for Craft CMS, version 4 or version 5. It helps create "edit this entry" links for your entry authors on the front-end pages of your site. It's design to work well with statically cached sites, like those using FastCGI Cache.

See the [Overview](#overview) section for more information.

The screenshot below is an example of how my blog looks to me when I'm logged into Craft CMS. A pencil icon link allows me to quickly jump to entry's edit page in the control panel.

![screenshot.png](screenshot.png)

## Requirements

This plugin requires Craft CMS 4.4.7.1 or later, and PHP 8.0.2 or later. It is also compatible with Craft CMS 5.x.

## Installation

You can install this plugin from the Plugin Store or with Composer.

#### From the Plugin Store

Go to the Plugin Store in your project’s Control Panel and search for “entry-editor-links”. Then press “Install”.

#### With Composer

Open your terminal and run the following commands:

```bash
# go to the project directory
cd /path/to/my-project.test

# tell Composer to load the plugin
composer require johnfmorton/craft-entry-editor-links

# tell Craft to install the plugin
./craft plugin/install entry-editor-links
```

### Overview

This plugin helps generate links to an entry's edit page within the Craft CMS control panel. While this functionality is easy to create in Twig templates, using the [`getCpEditUrl`](https://docs.craftcms.com/api/v4/craft-models-entrytype.html#method-getcpediturl) function. Using this function on statically cached pages risks exposing the entry's edit URL to users who don't have permission to edit the entry.

This plugin solves that problem by creating an API endpoint that returns the entry's edit URL _only for logged-in Craft users that have permission to edit the entry_. You can then use Javascript to render the edit link in the DOM. Sample Javascript to do this is in the [Using the plugin](#using-the-plugin) section below.

The edit button should only be rendered on the front end of the site, not when the entry is displayed in the Preview pane within the control panel. The plugin provides a Twig function, `isFrontEndPageView()`, that can be used to determine if a page is being rendered on the front end of the site. This conditional allows you to render the `data-edit-link` attribute only on the front end of the site.

### Plugin functionality

The plugin does two things:

1. It provides an endpoint that expects an entry ID and returns a JSON object with the entry's edit URL.
2. It also provides a Twig function, `isFrontEndPageView()` to determine if a page is being rendered on the front end of the site. This is to prevent the edit links from being displayed when a user has the preview pane open while editing an entry in the control panel or if the entry is being rendered on the front end of the site using a preview token.

### Using the plugin

The first step is to render the `data-edit-link` attribute on the element in your template to display the entry ID for the entry you wish to be able to edit. 

Do this by wrapping the attribute in the `isFrontEndPageView` conditional that checks if the page is being rendered on the front end of the site. 

```
{% if isFrontEndPageView() %}data-edit-link="{{ entry.id }}"{% endif %}
```

For example, if you have a list of entries, you can add the `data-edit-link` attribute to the element that wraps each entry. You can put this data attribute on the `article` element that wraps each entry.

```
<article {% if isFrontEndPageView() and (entry is defined) %} data-edit-link="{{ entry.id }}"{% endif %}>
    <h2>{{ entry.title }}</h2>
    <p>{{ entry.body }}</p>
</article>
```

Then, after a page loads, look for any instance of the `data-edit-link` attribute and query the plugin's API endpoint to get the entry's edit URL. If the user is logged in and has permission to edit the entry, the plugin will return the entry's edit URL. Then you can add a link to the edit page in the DOM for the entry.

Here's a basic example of how you can do this using JavaScript. You can add this to a JavaScript file that is loaded on the front end of your site. You will likely want to customize the styles and text of the edit link to match your site's design.

```
window.addEventListener('load', () => {
    // get all the elements with a data-attribute of 'edit-link'
    const editLinks = document.querySelectorAll('[data-edit-link]');
    // loop through the divs
    editLinks.forEach((editLink) => {
        // get the id from the data attribute
        const id = editLink.getAttribute('data-edit-link');
        // confirm the id is a number
        if (id && parseInt(id)) {
            // make a request to the plugin's API endpoint
            fetch('/actions/entry-editor-links/entry-processor/cp-link?id=' + id)
                .then((response) => {
                    // if the response is ok, return the json
                    if (response.ok) {
                        return response.json();
                    }
                    // otherwise, return an empty object
                    return null;
                })
                .then((data) => {
                    // data object will look like this: : { success: true, message: URL } or : { success: false, message: error message }
                    // if the data has success==true and a message, add a link to the edit page
                    if (data.success && data.message) {
                        // create an anchor element
                        const link = document.createElement('a');
                        // set the href attribute
                        link.setAttribute('href', data.message);
                        // set the text
                        link.innerText = 'Edit 📝';
                        // add some styles to the edit button
                        link.style.backgroundColor = '#f1f1f1';
                        link.style.color = '#333';
                        link.style.borderRadius = '5px';
                        link.style.border = '1px solid #ccc';
                        link.style.textDecoration = 'none';
                        link.style.boxShadow = '0 0 10px rgba(0,0,0,0.1)';
                        link.style.zIndex = '9999';
                        link.style.fontFamily = 'Arial, sans-serif';
                        link.style.fontSize = '14px';
                        // open the link in a new tab
                        link.setAttribute('target', '_blank');
                        // append the link to the div
                        editLink.appendChild(link);
                    }
                })
                .catch((error) => {
                    // log any errors
                    console.error(error);
                }
            );
        }
    })
});
```

### Using the plugin with FastCGI Cache

If you're using FastCGI Cache, you'll need to add a rule to prevent the plugin's API endpoint from being cached. This is because the plugin's API endpoint returns different data depending on whether the user is logged in and has permission to edit the entry. If the endpoint is cached, the edit link will be shown to users who don't have permission to edit the entry.

Here are examples of what that rule might look like for Apache and Nginx servers. Your server may require a different rules, but this should give you an idea of how to exclude `^/actions/entry-editor-links` URLs from being cached.

#### Using a `.htaccess` file with an Apache server


```
# Don't cache the Entry Editor Links API endpoint
# Uses the mod_headers Apache module
<IfModule mod_headers.c>
    <LocationMatch "^/actions/entry-editor-links">
        Header set Cache-Control "no-store, no-cache, must-revalidate, max-age=0"
    </LocationMatch>
</IfModule>
```

#### Using a `nginx.conf` file with an Nginx server

```
# Don't cache the Entry Editor Links API endpoint
location ~ ^/actions/entry-editor-links {
    set $nocache 1;
}
```

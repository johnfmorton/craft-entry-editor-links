# Entry Editor Link Plugin

This is a plugin for Craft CMS, version 4. It helps make front-end entry edit links for your entry authors compatible with statically cached sites.

## Requirements

This plugin requires Craft CMS 4.4.7.1 or later, and PHP 8.0.2 or later.

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
composer require john-f-morton/craft-entry-editor-links

# tell Craft to install the plugin
./craft plugin/install entry-editor-links
```

### Overview

This plugin helps generate links to the Craft CMS entry editor for a given entry. This functionality is easy to create in Twig templates, but if you have a page statically cached, like with FastCGI Cache, you could run into instances where the edit entry link is shown to a user who doesn't have permission to edit the entry.

This plugin solves that problem by providing a Twig function that can be used to determine if a page is being rendered on the front end of the site. If it is, you can render a `data-edit-link` attribute on the element in your template displaying the entry ID. Then, using JavaScript, you can query the plugin's API endpoint which will return the control panel edit URL if the user is logged in and has permission to edit the entry. Then you can add a link to the edit page in the DOM for the entry.

Exposing only an entry ID helps prevent leaking information about your site's structure to users who don't have permission to edit entries.

### Plugin functionality

The plugin does two things:

1. It provides an endpoint that expects an entry ID and returns a JSON object with the entry's edit URL.
2. It also provides a Twig function, `isFrontEndPageView()` to determine if a page is being rendered on the front end of the site. This is to prevent the edit links from being displayed when a user has the preview pane open while editing an entry in the control panel or if the entry is being rendered on the front end of the site using a preview token.

### Using the plugin

The first step is to render the `data-edit-link` attribute on the element in your template that displays the entry ID. You can do this by wrapping the attribute in a conditional that checks if the page is being rendered on the front end of the site. For example, you can put this data attribute on the `h1` element that displays the entry title.

```
{% if isFrontEndPageView() %}data-edit-link="{{ entry.id }}"{% endif %}
```

Then, after a page loads, look for any instance of the `data-edit-link` attribute and query the plugin's API endpoint to get the entry's edit URL. If the user is logged in and has permission to edit the entry, the plugin will return the entry's edit URL. Then you can add a link to the edit page in the DOM for the entry.

Here's an example of how you can do this using JavaScript:

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
                        link.innerText = '📝';
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

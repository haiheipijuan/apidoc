# CASHLINK API documentation source

Documentation source files are in `source/`.

We use Slate for generating HTML. See the Slate documentation for more info:

- https://github.com/lord/slate
- https://github.com/lord/slate/wiki/Markdown-Syntax
- https://github.com/lord/slate/wiki

To edit the API documentation and have the HTML rebuild automatically, run the development server:

```
./server.sh
```

This launches a Slate server on http://localhost:4567.

## Custom images, stylesheets, etc.

We automatically copy all files from https://github.com/lord/slate/tree/master/source that are not found in our own `source/` directory. If you want to modify any file from Slate's original source directory, simply copy it over and restart the server.

## Deployment

Deploy to GitHub Pages using:

```
./deploy.sh
```

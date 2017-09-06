# codemirror-customizer

A tool to create custom CodeMirror bundles

## Installation & usage

Simply unzip into a folder in your web server (or localhost). The `index.php` file should be accesible via web browser.

Open your browser and navigate to the folder where you put the files (for example http://localhost/codemirror-customizer). The script will automatically download and process the required files from cdnjs.com.

When the required files are in place, you will be taken to a screen where you will be able to pick a version of CodeMirror and include keymaps, language modes and other addons.

Select all the components that you want to bundle together and click the _Download bundle_ button at the bottom.

After a few seconds (depending on how many components you chose) your download will be ready, save the file and enjoy.

## Troubleshooting

If the script dies while fetching stuff, try increasing your [PHP execution-time limit](https://www.google.com.mx/search?q=php+increase+execution+time).

Also keep in mind that this project depends on cdnjs.com, so if the service is down (or dies) this script will not work at all.

If you find some other bug or have any suggestions feel free to file an issue and I'll try to help you; otherwise you may fork the repo if you like. Pull requests are welcome.

## License

MIT licensed.
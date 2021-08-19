# Dictionary Tools for Fastly

This project provides CLI tools for making working with [Fastly Edge Dictionaries](https://docs.fastly.com/en/guides/about-edge-dictionaries) more straightforward. 

## Installation

The easiest way to get started is through composer.

Alternatively, download this project to a system with PHP and Composer installed, and run `composer install` from the project root.

Once installed you can run the tool by calling `bin/main` or `vendor/bin/main` from your terminal. (if this doesn't work you may need to make the tool executable with `chmod +x bin/main`)

## Usage

There is currently one command:

```
dictionary:import [-s|--service_id SERVICE_ID] [-d|--dictionary_id DICTIONARY_ID] [--skip-header-row] [--] <file>
```

### Options

```
Arguments:
  file                               CSV input file

Options:
  -s, --service_id=SERVICE_ID        Fastly service ID
  -d, --dictionary_id=DICTIONARY_ID  Fastly dictionary ID
      --skip-header-row              Skip header row
```

The CSV should contain two columns:
 - The first column should contain the dictionary item key
 - The second column should contain the dictionary item value

The CSV can contain an optional header row (if so, use `--skip-header-row`), must be comma-delimited and does not currently support enclosure characters.

The CSV will be compared with the current dictionary, and items will be added and removed as necessary so that the dictionary matches the CSV contents. **Important:** this is a destructive operation. Ensure you have a backup of your dictionary before starting.

## Issues

If you identify any errors or have an idea for improving the plugin, please open an issue. We're excited to see what the community thinks of this project, and we would love your input!

Please note that this project is provided as-is and we cannot guarantee all bugs will be fixed.
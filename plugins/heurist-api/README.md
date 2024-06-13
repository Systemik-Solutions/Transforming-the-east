# Heurist API WordPress Plugin

This plugin provides a library for developers to access the Heurist API from Code. It handles the requests to the Heurist API
and parses the response in a consistent way.

## Components

### Client

The `HeuristClient` class provides ways for developers to configure the target Heurist host and database. It also
provides the functions for sending requests to Heurist.

### Service

The plugin contains a simple REST endpoint which can forward the requests to Heurist and get the responses. The service
provides a generic way to configure and access the Heurist API.

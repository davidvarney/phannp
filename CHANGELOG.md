# Changelog

All notable changes to this project will be documented in this file.

See also the [README.md](./README.md) "Changelog (recent)" section for a compact summary.

## [Unreleased]

## 2025-10-20

- API key handling change
  - The SDK now appends the client's `api_key` as a query parameter (`api_key`) on all requests.
  - For POST, PUT and DELETE requests the `api_key` is sent via Guzzle's `query` option and any trailing `/` on provided endpoints is trimmed before sending.
  - Migration note: If your code previously relied on the SDK placing the API key inside request bodies or using an `auth` array, update any tests or integrations to look for `api_key` in the request query string.

- Postcards resource update
  - `Postcards::create()` now requires an associative array of parameters (no longer accepts a plain size string). The `size` key is required (e.g. `['size' => '4x6']`).
  - Migration note: Update any code or tests that previously called `create('4x6')` to pass `create(['size' => '4x6'])` instead.

## 2025-10-09

- Refactor of Resources API
  - `Letters::create()` now accepts a `recipient` parameter (id or array) and supports `template`, `file` (path/resource/URL), `duplex`, `clearzone`, and other flags.
  - `Letters::post()` added for posting pre-merged PDF/DOC files (accepts country and file resource/URL).
  - `Postcards::create()` now requires an associative array of parameters and must include a `size` key (e.g. `['size' => '4x6']`). Other postcard parameters may be added in future SDK releases.
  - Tests and README updated to reflect these API changes.

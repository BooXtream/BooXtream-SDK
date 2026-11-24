# Changelog

All notable changes to this package are documented here. The format follows
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and the package uses
[Semantic Versioning](https://semver.org). Releases are git tags (`vX.Y.Z`).

## [3.0.0] - 2026-11-24

Breaking release. Requires PHP 8.3+ and Guzzle 7.15.2+ or 8.6+, and adds native parameter and return types (`declare(strict_types=1)`).

### Added
- PDF support. The service picks EPUB or PDF from the file extension; for a PDF, `send()` fills in
  `expirydays` (30) and `downloadlimit` (3) when not given, because the core API requires both for every PDF.

### Changed
- Requires Guzzle 7.15.2+ (security fixes in Guzzle and guzzlehttp/psr7) or Guzzle 8.6+. On Guzzle 8 the username and API key must be strings and the username must not contain a `:`, and a client passed in with a hand-built handler stack needs `GuzzleHttp\Middleware::auth()` in that stack, because Guzzle 8 applies the `auth` option there; `HandlerStack::create()` and the default client already include it.
- Format-neutral API: `setEpubFile()` is `setFile()`, `setStoredEpubFile()` is `setStoredFile()`, and the `epub` type is `direct`.
- All setters take a `string` and, like `Options::parseOptions()`, return `void`; failures throw. `send()` returns `Psr\Http\Message\ResponseInterface`.
- `BooXtreamClientInterface` no longer declares the constructor.
- Stored file names are URL-encoded per path segment, and an empty name or one with an empty, `.` or `..` path segment throws an `InvalidArgumentException`.
- `setStoredFile()` only strips a trailing `.epub` (`book.epub.v2` is no longer cut to `book`).
- The not-found `InvalidArgumentException` from a stored-file check carries the Guzzle exception as `getPrevious()`.

### Removed
- mobi: the `mobi` type throws an `InvalidArgumentException`. The BooXtream service no longer supports the `kf8mobi` option; requests that send it fail.
- The `epub` option; the service always delivers the file in its own format.

## [2.0.2] - 2026-11-24

### Security
- Requires Guzzle 7.15.2 or newer, which pulls in guzzlehttp/psr7 2.13 or newer, past the published Guzzle and psr7 advisories. No code changes; PHP 7.2.5 is still supported.

## Earlier releases

2.0.1 and older are documented by their git tags only.

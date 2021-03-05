# laravel-s3-uploads

[![PHP Unit](https://github.com/Saritasa/php-laravel-s3-uploads/workflows/PHP%20Unit/badge.svg)](https://github.com/Saritasa/php-laravel-s3-uploads/actions)
[![PHP CodeSniffer](https://github.com/Saritasa/php-laravel-s3-uploads/workflows/PHP%20Codesniffer/badge.svg)](https://github.com/Saritasa/php-laravel-s3-uploads/actions)
[![CodeCov](https://codecov.io/gh/Saritasa/php-laravel-s3-uploads/branch/master/graph/badge.svg)](https://codecov.io/gh/Saritasa/php-laravel-s3-uploads)
[![Release](https://img.shields.io/github/release/Saritasa/php-laravel-s3-uploads.svg)](https://github.com/Saritasa/php-laravel-s3-uploads/releases)
[![PHPv](https://img.shields.io/packagist/php-v/saritasa/laravel-s3-uploads.svg)](http://www.php.net)
[![Downloads](https://img.shields.io/packagist/dt/saritasa/laravel-s3-uploads.svg)](https://packagist.org/packages/saritasa/laravel-s3-uploads)

Laravel API for S3 uploads

## Usage

Install the ```saritasa/laravel-s3-uploads``` package:

```bash
$ composer require saritasa/laravel-s3-uploads
```

Configure Your `Storage::cloud()` disk for AWS S3 accroding to [Laravel Manual](https://laravel.com/docs/filesystem#driver-prerequisites)

This package exposes `POST <API_PREFIX>/uploads/tmp` route, using [Dingo/Api](https://github.com/dingo/api) router.
It accepts `application/json` request in form:
```json
{
  "fileName": "image.jpg"
}
```
and returns response with [S3 PreSigned URLs](https://laravel.com/docs/6.x/filesystem#file-urls) as 
```json
{
  "uploadUrl": "https://my-bucket.s3-us-west.amazonaws.com/tmp/1341234uoi123lhkj1.jpg?<WRITE_SIGNATURE=...>",
  "validUntil": "2017-04-12T23:20:50.52Z",
  "fileUrl": "https://my-bucket.s3-us-west.amazonaws.com/tmp/1341234uoi123lhkj1.jpg?<READ_SIGNATURE=...>"
}
```
* **uploadUrl** can be used with `PUT <uploadUrl>` on frontend to upload URL to S3 directly.  
* **fileUrl** is a presigned URL, that can be used to read this file on frontend after upload
 (supposing, that your S3 bucket has default policy to set 'private' ACL for new files).
 

### Configuration
You can use `config/media.php` to change default uploads path within bucket or presigned urls expiration timeouts.

## Contributing
See [CONTRIBUTING](CONTRIBUTING.md) and [Code of Conduct](CONDUCT.md),
if you want to make contribution (pull request)
or just build and test project on your own.

## Resources

* [Changes History](CHANGES.md)
* [Bug Tracker](https://github.com/Saritasa/php-laravel-s3-uploads/issues)
* [Authors](https://github.com/Saritasa/php-laravel-s3-uploads/contributors)

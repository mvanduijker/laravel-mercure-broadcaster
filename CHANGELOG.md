# Changelog

All notable changes to `laravel-mercure-broadcaster` will be documented in this file

## 3.7.0 - 2025-02-28

- Add support for Laravel 12

## 3.6.1 - 2024-11-20

- Add support for lcobucci/jwt v5

## 3.6.0 - 2023-03-13

- Add support for Laravel 11
- Added PHP 8.3 in the pipeline

## 3.5.0 - 2023-02-14

- Add support for Laravel 10
- Added PHP 8.2 in the pipeline
- Fixed tests with latest mercure release (change of log format)

## 3.4.0 - 2022-02-09

- Add support for Laravel 9

## 3.3.0 - 2021-06-28

- Fix using deprecated code from Mercure library
- Prefer using Laravel Broadcaster contract over extending the base class
- Add service that generates the JWT so we are able to override to use a custom implementation
- Fix starting mercure server in tests

## 3.2.0 - 2020-12-01

- add support for php 8
- dropped support for php 7.3

## 3.1.1 - 2020-12-01

- fix errors with lcobucci/jwt version 3.4.*

## 3.1.0 - 2020-09-08

- support for Laravel 8
- dropped support php 7.2
- dropped support Laravel 5.x

## 3.0.0 - 2020-06-01

- support for Mercure 0.10, check https://mercure.rocks/docs/UPGRADE for more information

## 2.1.0 - 2020-03-03

- support for laravel 7.0
- dropped support php 7.1
- added support php 7.4
- dropped support for laravel 5.6 and 5.7

## 2.0.0 - 2019-11-16

Compatible with Mercure 0.8 see https://github.com/dunglas/mercure/blob/master/docs/UPGRADE.md for upgrade instructions.

TLDR; Update Mercure to latest version and update mercure endpoint url from /hub to /.well-known/mercure in your app. 
See the readme in the root of this repository how to do this.

## 1.1.0 - 2019-09-03

- support for laravel 6.0

## 1.0.0 - 2019-05-19

- initial release

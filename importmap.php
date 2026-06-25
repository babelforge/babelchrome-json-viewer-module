<?php

/**
 * Returns the importmap for this module.
 *
 * The JSON viewer currently vendors its browser library as a local IIFE bundle,
 * so no external JavaScript package is required here.
 *
 * @return array<string, array{
 *     path: string,
 *     type?: 'js'|'css'|'json',
 *     entrypoint?: bool,
 * }|array{
 *     version: string,
 *     package_specifier?: string,
 *     type?: 'js'|'css'|'json',
 *     entrypoint?: bool,
 * }>
 */
return [];

<?php

namespace WP2StaticAdvancedCrawling;

class CrawlQueue {

    /**
     * Set crawled_time to NULL for the URL.
     *
     * @param string $url The URL.
     */
    public static function clearCrawledTime( string $url ) : void {
        global $wpdb;

        $table_name = $wpdb->prefix . 'wp2static_urls';
        $query = $wpdb->prepare(
            "UPDATE $table_name SET crawled_time = NULL WHERE url = %s",
            $url
        );
        $wpdb->query( $query );
    }

    /**
     * Set crawled_time to NOW() for each URL.
     *
     * @param string[] $urls List of URLs.
     */
    public static function updateCrawledTimes( array $urls ) : void {
        global $wpdb;

        $table_name = $wpdb->prefix . 'wp2static_urls';
        $wpdb->query( 'START TRANSACTION' );
        foreach ( $urls as $url ) {
            $query = $wpdb->prepare(
                "UPDATE $table_name SET crawled_time = NOW() WHERE url = %s",
                $url
            );
            $wpdb->query( $query );
        }
        $wpdb->query( 'COMMIT' );
    }

    /**
     * Get a chunk of URLs to crawl.
     *
     * @param string $crawl_start_time Start time of the crawl.
     * @param int $size Max number of URLs to return.
     * @return array<string> Array of URLs.
     */
    public static function getChunk( string $crawl_start_time, int $size ) : array {
        global $wpdb;

        $table_name = $wpdb->prefix . 'wp2static_urls';

        $query = $wpdb->prepare(
            "SELECT id, url FROM $table_name
             WHERE crawled_time IS NULL OR crawled_time <= %s
             LIMIT $size",
            $crawl_start_time
        );
        $rows = $wpdb->get_results( $query );

        $urls = [];
        foreach ( $rows as $row ) {
            $urls[ $row->id ] = $row->url;
        }
        return $urls;
    }

    /**
     * Get a chunk of URLs to crawl with null crawled_time.
     *
     * @param int $size Max number of URLs to return.
     * @return array<string> Array of URLs.
     */
    public static function getChunkNulls( int $size ) : array {
        global $wpdb;

        $table_name = $wpdb->prefix . 'wp2static_urls';

        $rows = $wpdb->get_results(
            "SELECT id, url FROM $table_name
             WHERE crawled_time IS NULL
             LIMIT $size"
        );

        $urls = [];
        foreach ( $rows as $row ) {
            $urls[ $row->id ] = $row->url;
        }
        return $urls;
    }

}

<?php

require 'vendor/autoload.php';

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;

class Pokemon {
    public $name;
    public $generation;
    public $attacks;
}

class PokemonLibrary {
    public static function getPokemonList() {
        $client = HttpClient::create();
        $response = $client->request('GET', 'https://www.pokebip.com/page/jeuxvideo/pokemon_go/pokemon');
        $content = $response->getContent();

        $crawler = new Crawler($content);

        $pokemonList = [];

        $tables = $crawler->filter('table');

        $tables->each(function (Crawler $table) use (&$pokemonList) {
            $generation = $table->previousAll()->filter('h2')->text();

            $table->filter('tr')->each(function (Crawler $row) use (&$pokemonList, $generation) {
                $columns = $row->filter('td');

                if ($columns->count() >= 4) {
                    $pokemon = new Pokemon();
                    $pokemon->name = $columns->eq(1)->text();
                    $pokemon->generation = $generation;

                    $attacks = $columns->eq(3)->filter('a')->each(function (Crawler $link) {
                        return $link->text();
                    });

                    $pokemon->attacks = $attacks;

                    $pokemonList[] = $pokemon;
                }
            });
        });

        return $pokemonList;
    }
}

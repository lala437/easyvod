<?php


namespace EasyVod;


interface Collect
{
    public function VodList();

    public function VodMultiList();

    public function VodPlay();

    public function VodBanner();

    public function VodSearch();
}
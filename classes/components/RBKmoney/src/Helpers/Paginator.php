<?php

namespace src\Helpers;

class Paginator
{

    /**
     * Паттерн для замены номера страницы
     */
    const NUM_PLACEHOLDER = '(:num)';

    /**
     * Общее количество записей
     *
     * @var int
     */
    private $totalItems;

    /**
     * Общее количество страниц
     *
     * @var int
     */
    private $numPages;

    /**
     * Количество записей на странице
     *
     * @var int
     */
    private $itemsPerPage;

    /**
     * Номер текущей страницы
     *
     * @var int
     */
    private $currentPage;

    /**
     * Шаблон ссылки
     *
     * @var string
     */
    private $urlPattern;

    /**
     * Максимальное количество ссылок в пагинации
     *
     * @var int
     */
    private $maxPagesToShow = 5;

    /**
     * @param int    $totalItems
     * @param int    $itemsPerPage
     * @param int    $currentPage
     * @param string $urlPattern
     */
    public function __construct($totalItems, $itemsPerPage, $currentPage, $urlPattern = '')
    {
        $this->totalItems = $totalItems;
        $this->itemsPerPage = $itemsPerPage;
        $this->currentPage = $currentPage;
        $this->urlPattern = $urlPattern;

        $this->updateNumPages();
    }

    /**
     * @return void
     */
    private function updateNumPages()
    {
        if (0 === $this->itemsPerPage) {
            $this->numPages = 0;
        } else {
            $this->numPages = (int) ceil($this->totalItems / $this->itemsPerPage);
        }
    }

    /**
     * @param int $pageNum
     *
     * @return string
     */
    public function getPageUrl($pageNum)
    {
        return str_replace(self::NUM_PLACEHOLDER, $pageNum, $this->urlPattern);
    }

    /**
     * @return string | null
     */
    public function getPrevUrl()
    {
        if (!$this->getPrevPage()) {
            return null;
        }

        return $this->getPageUrl($this->getPrevPage());
    }

    /**
     * @return int | null
     */
    public function getPrevPage()
    {
        if ($this->currentPage > 1) {
            return $this->currentPage - 1;
        }

        return null;
    }

    /**
     * @return string | null
     */
    public function getNextUrl()
    {
        if (!$this->getNextPage()) {
            return null;
        }

        return $this->getPageUrl($this->getNextPage());
    }

    /**
     * @return int | null
     */
    public function getNextPage()
    {
        if ($this->currentPage < $this->numPages) {
            return $this->currentPage + 1;
        }

        return null;
    }

    /**
     * @return array
     */
    public function getPages()
    {
        $pages = [];

        if ($this->numPages <= 1) {
            return $pages;
        }

        if ($this->numPages <= $this->maxPagesToShow) {
            for ($i = 1; $i <= $this->numPages; $i++) {
                $pages[] = $this->createPage($i, $i == $this->currentPage);
            }
        } else {
            $numAdjacents = (int) floor(($this->maxPagesToShow - 3) / 2);

            if ($this->currentPage + $numAdjacents > $this->numPages) {
                $slidingStart = $this->numPages - $this->maxPagesToShow + 2;
            } else {
                $slidingStart = $this->currentPage - $numAdjacents;
            }
            $slidingStart = $slidingStart < 2 ? 2 : $slidingStart;

            $slidingEnd = $slidingStart + $this->maxPagesToShow - 3;
            $slidingEnd = $slidingEnd >= $this->numPages ? $this->numPages - 1 : $slidingEnd;

            $pages[] = $this->createPage(1, $this->currentPage == 1);

            if ($slidingStart > 2) {
                $pages[] = $this->createPageEllipsis();
            }

            for ($i = $slidingStart; $i <= $slidingEnd; $i++) {
                $pages[] = $this->createPage($i, $i == $this->currentPage);
            }

            if ($slidingEnd < $this->numPages - 1) {
                $pages[] = $this->createPageEllipsis();
            }

            $pages[] = $this->createPage($this->numPages, $this->currentPage == $this->numPages);
        }

        return $pages;
    }

    /**
     * @param int  $pageNum
     * @param bool $isCurrent
     *
     * @return array
     */
    private function createPage($pageNum, $isCurrent = false)
    {
        return [
            'num' => $pageNum,
            'url' => $this->getPageUrl($pageNum),
            'isCurrent' => $isCurrent,
        ];
    }

    /**
     * @return array
     */
    private function createPageEllipsis()
    {
        return [
            'num' => '...',
            'url' => null,
            'isCurrent' => false,
        ];
    }

}
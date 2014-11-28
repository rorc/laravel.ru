<?php namespace LaravelRU\Tips;

use LaravelRU\Core\Repository\BaseRepository;
use Tip;

class TipsRepo extends BaseRepository
{

    public function __construct(Tip $tip)
    {
        $this->model = $tip;
    }

    /**
     * Последние "а знаете ли вы что"
     *
     * @param int $num
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getLastTips($num = 10)
    {
        return $this->model->orderBy("published_at", "DESC")->limit($num)->get();
    }

}
    
?> 
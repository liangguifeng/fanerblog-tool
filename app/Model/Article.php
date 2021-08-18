<?php


namespace App\Model;


class Article extends Model
{
    protected $table = 'biz_article';

    protected $guarded = [];

    //文章分类
    public function type()
    {
        return $this->belongsTo(Type::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'biz_article_tags', 'article_id', 'tag_id');
    }

}
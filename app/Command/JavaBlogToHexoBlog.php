<?php

declare(strict_types=1);

namespace App\Command;

use App\Model\Article;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;
use Psr\Container\ContainerInterface;

/**
 * @Command
 */
class JavaBlogToHexoBlog extends HyperfCommand
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * 定义文件存储路径
     *
     * @var string
     */
    protected $path = '/Users/liang/web/hexo-fanerblog/';

    /**
     * JavaBlogToHexoBlog constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct('tool:java_blog_to_hexo');
    }

    /**
     * 命令说明
     */
    public function configure()
    {
        parent::configure();
        $this->setDescription('将原java-fanerblog的文章迁移到hexo的md文件');
    }


    public function handle()
    {
        $article = Article::query()->first();

        try {
            $articleFile = fopen($this->path . $article->title . '.md', "w");

            //定义标题
            $title = "---
title: $article->title
date: $article->create_time
categories: ['php']
tags: ['hello-world', 'blog']
cover: $article->cover_image
---

";
            //写入标题
            fwrite($articleFile, $title);
            //写入markdown内容
            fwrite($articleFile, $article->content_md);
            fclose($articleFile);
        } catch (\Exception $exception) {
            $this->error('写入md文件失败，原因是：' . $exception->getMessage());
        }
    }
}

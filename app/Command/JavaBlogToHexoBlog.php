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
     * 定义发布文件存储路径
     *
     * @var string
     */
    protected $publishPath = '/Users/liang/web/fanerblog/source/_posts/';

    /**
     * 定义未发布存储路径
     *
     * @var string
     */
    protected $noPublishPath = '/Users/liang/web/fanerblog/source/_drafts/';

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
        Article::query()
            ->where('is_markdown', true)
            ->orderBy('create_time')
            ->chunkById(20, function ($articles) {
                foreach ($articles as $article) {
                    try {
                        if ($article->status) {
                            $articleFile = fopen($this->publishPath . $article->title . '.md', "w");
                        } else {
                            $articleFile = fopen($this->noPublishPath . $article->title . '.md', "w");
                        }

                        $type = $article->type->name ?? '未分类';

                        $tags = '';
                        foreach ($article->tags->pluck('name')->toArray() as $value) {
                            $tags .= "'" . $value . "'" . ',';
                        }

                        $tags = rtrim($tags, ',');

                        if ($article->status) {
                            //定义标题
                            $title = "---
title: $article->title
date: $article->create_time
categories: ['$type']
toc: true
tags: [$tags]
cover: $article->cover_image
---

";
                        } else {
                            //定义标题
                            $title = "---
title: $article->title
date: $article->create_time
categories: ['$type']
toc: true
tags: [$tags]
cover: $article->cover_image
password: fanerblog
abstract: 这里有加密的东西，需要密码才能继续阅读！
message: 该文章已加密，请输入阅读密码！
wrong_pass_message: 您输入的密码错误，请检查并重试。
---

";
                        }


                        //写入标题
                        fwrite($articleFile, $title);
                        //写入markdown内容
                        fwrite($articleFile, $article->content_md);
                        fclose($articleFile);
                    } catch (\Exception $exception) {
                        $this->error('写入md文件失败，原因是：' . $exception->getMessage());
                    }
                }
            });
    }
}

<?php
namespace App\Repos;

use App\Models\Post;
use Illuminate\Support\Facades\Redis;


class PostRepo
{
    protected Post $post;
    protected string $trendingPostsKey = 'popular_posts';

    public function __construct(Post $post)
    {
        $this->post = $post;
    }

    public function getById(int $id,array $columns = ['*'])
    {
       $cacheKey = 'post_'.$id;
       if(Redis::exists($cacheKey)) {
           return unserialize(Redis::get($cacheKey));
       }
       $post = $this->post->select($columns)->find($id);
       if(!$post) {
           return null;
       }
       Redis::setex($cacheKey,1*60*60,serialize($post));
       return $post;
    }

    public function getByManyId(array $ids,array $columns = ['*'],callable $callback =  null)
    {
        $query = $this->post->select($columns)->whereIn('id',$ids);
        if($query) {
            $query = $callback($query);
        }
        return $query->get();
    }

    public function addViews(Post $post)
    {
        $post->increment('views');
        if($post->save()) {
            //
            Redis::zincrby('popular_posts',1,$post->id);
        }
        return $post->views;
    }

    public function trending($num)
    {
        $cacheKey = $this->trendingPostsKey.'_'.$num;
        if(Redis::exists($cacheKey)) {
            return unserialize(Redis::get($cacheKey));
        }
        $postIds = Redis::zrevrange($this->trendingPostsKey,0,$num-1);
        if(!$postIds) {
            return null;
        }
        $idsStr = implode(',',$postIds);
        $posts = $this->getByManyId($postIds,['*'],function($query)use($idsStr){
            return $query->orderByRaw('filed(`id`,'.$idsStr.')');
        });
        Redis::setex($cacheKey,1*10,serialize($posts));
        return $posts;
    }
}


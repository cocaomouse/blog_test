<?php

namespace App\Http\Controllers;

use App\Events\PostViewed;
use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Support\Facades\Redis;
use App\Repos\PostRepo;

class PostController extends Controller
{
    //
    protected PostRepo $postRepo;
    public int $num;

    public function __construct(PostRepo $postRepo)
    {
        $this->postRepo = $postRepo;
        $this->num = 10;
    }

    public function show($id)
    {
        $post = $this->postRepo->getById($id);
        //$views = $this->postRepo->addViews($post);
        event(new PostViewed($post));

        return "Show Post # {$post->id},Views:{$post->views}";
    }

    public function popular()
    {
        //
        $posts = $this->postRepo->trending($this->num);
        if($posts) {
            dd($posts->toArray());
        }
    }


}

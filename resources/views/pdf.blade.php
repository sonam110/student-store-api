<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cv</title>
    <style type="text/css">
        .container{
    width: 1043px;
    margin: 2rem auto;
    border: 1px solid #808080a1;
    
}
.row{
    display: flex;
}
.language-known{
    justify-content: space-between;
        margin: 2rem 0rem;
}
.col-3{
    width: 25%;

}
.col-9{
    width: 75%;
    padding: 0rem 1rem;

}
.content{
      display: flex;
    justify-content: space-between;
        margin: 1rem 0rem;

} 
.user-image img{
        width: 50%;
        margin: 1rem auto;
        display: flex;
        text-align: center;
        align-items: center;
            border-radius: 50%;
}

.right-side{
    border: 1px solid #808080a1;
    background-color: #e5e0e0d9;
}
.summery1 {
    padding: 1rem;
    font-size: 13px;
    color: grey;
    font-family: system-ui;
}



.certificate{
    width: 25%;
}
.icon-section-row{
   justify-content: space-between;
    margin: 1rem 0rem;
}


.col-right{
    margin-right: 1rem;
}
.heading1 h3{
    text-align: center;
}
.icon{
    margin: 2rem 0rem;
}
.col-10{
    margin-right: 5rem;
}
.link li{
    list-style: none;
}
.link {
    margin-bottom:1rem;
}

.fa-circle{
    font-size: 12px;
    color: #00adff;
}
.testing {
    color: grey;
}

.heading h3{
        color: #00adff;
}

h5 ,p{
    margin: 0rem;
}
.fa-square{
        font-size: 12px;
        color: #00adff;
        margin-right: .3rem;
}
.icon-display{
    position: relative;
    bottom: 2rem;
    color: #00adff;
    font-size: 13px;
}

span{
    font-size: 13px;
    color: grey;
}
.circle-image{
        width: 100%;
        height: 110px;
            border-radius: 0px 0px 121px 122px;

}

.image-content-section{
    position: relative;
    bottom: 7rem;

}
.test{
        height: 210px;
}
.test67{
    padding-left: 1rem;
}


  
    </style>
</head>

<body>
    <div class="container">
        <div class="row">
            <div class="col-3 right-side">
                <div class="test">
                    <img class="circle-image" src="{{$user->profile_pic_thumb_path}}" alt="">
                    <div class="image-content-section">
                        <div class="heading1">
                            <h3>Work Experience</h3>
                        </div>
                        <div class="user-image">
                            <img src="./images/user.png" alt="">
                        </div>
                    </div>
                </div>
                <div class="test67">
                    <div class="heading2">
                        <h3>Work Experience</h3>
                    </div>
                </div>
                <div class="test67">
                    <div class="row icon-section-row">
                        <div class="col-2">

                            <div class="icon">
                                <i class="fas fa-user icon-display"></i>
                            </div>
                            <div class="icon">
                                <i class="fas fa-home icon-display"></i>
                            </div>
                            <div class="icon">
                                <i class="fas fa-phone-alt icon-display"></i>
                            </div>
                            <div class="icon">
                                <i class="fab fa-linkedin icon-display"></i>
                            </div>
                            <div class="icon">
                                <i class="fas fa-envelope-square icon-display"></i>

                            </div>

                            <div class="icon">
                                <i class="fas fa-calendar-alt icon-display"></i>
                            </div>
                            <div class="icon">
                                <i class="fas fa-map-marker-alt icon-display"></i>
                            </div>
                            <div class="icon">
                                <i class="fas fa-user-friends icon-display"></i>
                            </div>
                            <div class="icon">
                                <i class="fas fa-car icon-display"></i>
                            </div>
                        </div>

                        <div class="col-10">
                            <div class="link">
                                <h5>Lorem ipsum dolor sit.</h5>
                                <span>Lorem ipsum dolor sit.</span>
                            </div>
                            <div class="link">
                                <h5>Lorem ipsum dolor sit.</h5>
                                <span>Lorem ipsum dolor sit.</span>
                            </div>
                            <div class="link">
                                <h5>Lorem ipsum dolor sit.</h5>
                                <span>Lorem ipsum dolor sit.</span>
                            </div>
                            <div class="link">
                                <h5>Lorem ipsum dolor sit.</h5>
                                <span>Lorem ipsum dolor sit.</span>
                            </div>
                            <div class="link">
                                <h5>Lorem ipsum dolor sit.</h5>
                                <span>Lorem ipsum dolor sit.</span>
                            </div>
                            <div class="link">
                                <h5>Lorem ipsum dolor sit.</h5>
                                <span>Lorem ipsum dolor sit.</span>
                            </div>
                            <div class="link">
                                <h5>Lorem ipsum dolor sit.</h5>
                                <span>Lorem ipsum dolor sit.</span>
                            </div>
                            <div class="link">
                                <h5>Lorem ipsum dolor sit.</h6>
                                    <span>Lorem ipsum dolor sit.</span>
                            </div>
                            <div class="link">
                                <h5>Lorem ipsum dolor sit.</h5>
                                <span>Lorem ipsum dolor sit.</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="test67">
                    <div class="heading3">
                        <h3>Work Experience</h3>
                    </div>
                </div>
                <div class="test67">
                    <div class="link">

                        <li> <i class="fas fa-square"></i>
                            lorem
                        </li>
                        <li> <i class="fas fa-square"></i>
                            lorem
                        </li>
                        <li><i class="fas fa-square"></i>
                            lorem
                        </li>

                    </div>
                </div>
                <div class="test67">
                    <div class="row language-known">
                        <div class="col-6">

                            <li> natus iusto </li>
                            <li> natus iusto </li>
                            <li> natus iusto </li>
                            <li> natus iusto </li>


                        </div>
                        <div class="col-6 col-right">
                            <div class="icon2">
                                <i class="fas fa-circle"></i>
                                <i class="fas fa-circle"></i>
                                <i class="fas fa-circle"></i>
                                <i class="fas fa-circle"></i>
                                <i class="fas fa-circle"></i>

                            </div>
                            <div class="icon2">
                                <i class="fas fa-circle"></i>
                                <i class="fas fa-circle"></i>
                                <i class="fas fa-circle"></i>
                                <i class="fas fa-circle"></i>
                                <i class="fas fa-circle testing"></i>

                            </div>
                            <div class="icon2">
                                <i class="fas fa-circle"></i>
                                <i class="fas fa-circle"></i>
                                <i class="fas fa-circle"></i>
                                <i class="fas fa-circle"></i>
                                <i class="fas fa-circle testing"></i>

                            </div>
                            <div class="icon2">
                                <i class="fas fa-circle"></i>
                                <i class="fas fa-circle"></i>
                                <i class="fas fa-circle"></i>
                                <i class="fas fa-circle testing"></i>
                                <i class="fas fa-circle testing"></i>

                            </div>

                        </div>
                    </div>
                </div>


            </div>
            <div class="col-9">
                <p class="summery1">
                    Lorem ipsum dolor sit amet consectetur adipisicing elit. Quam,
                    dolores quibusdam. Deserunt in totam ipsum rem laboriosam repudiandae, tempore,
                    asperiores veritatis aspernatur rerum earum laudantium fuga unde facere repellendus beatae alias
                    quo impedit inventore amet sequi. Adipisci eos sequi porro qui. Saepe quas ad iure vero quia
                    repellat
                    suscipit eius blanditiis dolor illo dolorem, adipisci omnis sit libero nisi est in inventore cumque
                    obcaecati
                    accusantium! Velit libero non voluptas unde repudiandae esse odit in! Inventore nisi atque ullam
                    maiores
                    assumenda cumque itaque quam perferendis voluptatibus nihil esse incidunt
                    laudantium labore explicabo ab harum, vel alias! Pariatur nesciunt cupiditate accusamus maxime?

                </p>


                <hr>

                <div class="heading">
                    <h3>Work Experience</h3>
                    <div class="content">
                        <div class="h5">
                            <h5>Junior Software Developer</h5>
                            <p>Lorem ipsum dolor sit amet consectetur adipisicing.</p>
                        </div>
                        <div class="date">
                            <p>12/20/2020</p>
                        </div>

                    </div>
                    <div class="content">
                        <div class="h5">
                            <h5>Junior Software Developer</h5>
                            <p>Lorem ipsum dolor sit amet consectetur adipisicing.</p>
                        </div>
                        <div class="date">
                            <p>12/20/2020</p>
                        </div>

                    </div>
                </div>
                <hr>
                <div class="heading">
                    <h3>Work Experience lorem </h3>
                    <div class="content">
                        <div class="h5">
                            <h5>Junior Software Developer</h5>
                            <p>Lorem ipsum dolor sit amet consectetur adipisicing.</p>
                        </div>
                        <div class="date">
                            <p>12/20/2020</p>
                        </div>

                    </div>

                    <p>Lorem ipsum dolor, sit amet consectetur adipisicing elit. Delectus at ea, perferendis ab
                        corporis
                        consectetur voluptatem possimus. Aut deleniti sunt magnam et, nulla consequatur esse itaque
                        fugit
                        obcaecati debitis neque molestias corporis perferendis aliquam pariatur quo aspernatur,
                        quibusdam,
                        error quidem Lorem, ipsum dolor sit amet consectetur adipisicing elit. Amet dignissimos
                        quasi velit similique perferendis iure natus iusto ipsa beatae quam.
                    </p>


                </div>
                <div class="heading">
                    <h3>Work Experience lorem </h3>
                    <div class="content">
                        <div class="h5">
                            <h5>Junior Software Developer</h5>
                            <p>Lorem ipsum dolor sit amet consectetur adipisicing.</p>
                        </div>
                        <div class="date">
                            <p>12/20/2020</p>
                        </div>

                    </div>
                    <div class="list-section">
                        <li> natus iusto ipsa beatae quam</li>
                        <li> natus iusto ipsa beatae quam</li>
                        <li> natus iusto ipsa beatae quam</li>
                        <li> natus iusto ipsa beatae quam</li>
                    </div>
                </div>
                <div class="heading">
                    <h3>Work Experience lorem </h3>
                    <div class="content">
                        <div class="h5">
                            <h5>Junior Software Developer</h5>
                            <p>Lorem ipsum dolor sit amet consectetur adipisicing.</p>
                        </div>
                        <div class="date">
                            <p>12/20/2020</p>
                        </div>

                    </div>
                    <div class="list-section">
                        <li> natus iusto ipsa beatae quam</li>
                        <li> natus iusto ipsa beatae quam</li>
                        <li> natus iusto ipsa beatae quam</li>
                        <li> natus iusto ipsa beatae quam</li>
                    </div>
                </div>

                <hr>
                <div class="heading">
                    <h3>Work Experience lorem </h3>
                    <div class="content">
                        <div class="h5">
                            <h5>Junior Software Developer</h5>
                            <p>Lorem ipsum dolor sit amet consectetur adipisicing.</p>
                        </div>
                        <div class="date">
                            <p>12/20/2020</p>
                        </div>

                    </div>
                </div>
                <hr>


                <div class="heading">
                    <h3>Work Experience lorem </h3>

                </div>
                <div class="row section">
                    <div class="col-3 certificate">
                        <li> natus iusto </li>
                        <li> natus iusto </li>
                        <li> natus iusto </li>
                        <li> natus iusto </li>


                    </div>
                    <div class="col-9">
                        <div class="icon3">
                            <i class="fas fa-circle"></i>
                            <i class="fas fa-circle"></i>
                            <i class="fas fa-circle"></i>
                            <i class="fas fa-circle"></i>
                            <i class="fas fa-circle"></i>

                        </div>
                        <div class="icon3">
                            <i class="fas fa-circle"></i>
                            <i class="fas fa-circle"></i>
                            <i class="fas fa-circle"></i>
                            <i class="fas fa-circle"></i>
                            <i class="fas fa-circle testing"></i>

                        </div>
                        <div class="icon3">
                            <i class="fas fa-circle"></i>
                            <i class="fas fa-circle"></i>
                            <i class="fas fa-circle"></i>
                            <i class="fas fa-circle testing"></i>
                            <i class="fas fa-circle testing"></i>

                        </div>
                        <div class="icon3">
                            <i class="fas fa-circle"></i>
                            <i class="fas fa-circle"></i>
                            <i class="fas fa-circle"></i>
                            <i class="fas fa-circle testing"></i>
                            <i class="fas fa-circle testing"></i>

                        </div>

                    </div>
                </div>

                <hr>
                <div class="heading">
                    <h3>Work Experience lorem </h3>
                    <div class="content">
                        <div class="h5">
                            <h5>Junior Software Developer</h5>
                            <p>Lorem ipsum dolor sit amet consectetur adipisicing.</p>
                            <p>Lorem ipsum dolor sit amet consectetur adipisicing.</p>
                            <p>Lorem ipsum dolor sit amet consectetur adipisicing.</p>
                        </div>
                        <div class="date">
                            <p>12/20/2020</p>
                        </div>

                    </div>
                </div>


            </div>

        </div>




</body>

</html>
<!DOCTYPE html>
<html lang="en" >
<head>
  <meta charset="UTF-8">
  <title>Scrap Data </title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/meyer-reset/2.0/reset.min.css">
<link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css'>
<style type="text/css">
  @import url(https://fonts.googleapis.com/css?family=Roboto:400,300,100,500,700,900);
.scrap-form {
  margin-top: 30px;
}
.scrap-form .input-block {
  background-color: rgba(255, 255, 255, 0.8);
  border: solid 1px #FF512F;
  width: 100%;
  height: 60px;
  padding: 25px;
  position: relative;
  margin-bottom: 20px;
  -moz-transition: all 0.3s ease-out;
  -o-transition: all 0.3s ease-out;
  -webkit-transition: all 0.3s ease-out;
  transition: all 0.3s ease-out;
}
.scrap-form .input-block.focus {
  background-color: #fff;
  border: solid 1px #fb2900;
}
.scrap-form .input-block.textarea {
  height: auto;
}
.scrap-form .input-block.textarea .form-control {
  height: auto;
  resize: none;
}
.scrap-form .input-block label {
  position: absolute;
  left: 25px;
  top: 25px;
  display: block;
  margin: 0;
  font-weight: 300;
  z-index: 1;
  color: #333;
  font-size: 18px;
  line-height: 10px;
}
.scrap-form .input-block .form-control {
  background-color: transparent;
  padding: 0;
  border: none;
  -moz-border-radius: 0;
  -webkit-border-radius: 0;
  border-radius: 0;
  -moz-box-shadow: none;
  -webkit-box-shadow: none;
  box-shadow: none;
  height: auto;
  position: relative;
  z-index: 2;
  font-size: 18px;
  color: #333;
}
.scrap-form .input-block .form-control:focus label {
  top: 0;
}
.scrap-form .square-button {
  background-color: rgba(255, 255, 255, 0.8);
  color: #fb2900;
  font-size: 26px;
  text-transform: uppercase;
  font-weight: 700;
  text-align: center;
  -moz-border-radius: 2px;
  -webkit-border-radius: 2px;
  border-radius: 2px;
  -moz-transition: all 0.3s ease;
  -o-transition: all 0.3s ease;
  -webkit-transition: all 0.3s ease;
  transition: all 0.3s ease;
  padding: 0 60px;
  height: 60px;
  border: none;
  width: 100%;
}
.scrap-form .square-button:hover, .scrap-form .square-button:focus {
  background-color: white;
}

@media (min-width: 768px) {
  .contact-wrap {
    width: 60%;
    margin: auto;
  }
}
/*----page styles---*/
body {
  background-image: url('data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4gPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGRlZnM+PGxpbmVhckdyYWRpZW50IGlkPSJncmFkIiBncmFkaWVudFVuaXRzPSJvYmplY3RCb3VuZGluZ0JveCIgeDE9IjAuMCIgeTE9IjAuNSIgeDI9IjEuMCIgeTI9IjAuNSI+PHN0b3Agb2Zmc2V0PSIwJSIgc3RvcC1jb2xvcj0iI2ZmNTEyZiIvPjxzdG9wIG9mZnNldD0iMTAwJSIgc3RvcC1jb2xvcj0iI2RkMjQ3NiIvPjwvbGluZWFyR3JhZGllbnQ+PC9kZWZzPjxyZWN0IHg9IjAiIHk9IjAiIHdpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiIGZpbGw9InVybCgjZ3JhZCkiIC8+PC9zdmc+IA==');
  background-size: 100%;
  background-image: -webkit-gradient(linear, 0% 50%, 100% 50%, color-stop(0%, #ff512f), color-stop(100%, #dd2476));
  background-image: -moz-linear-gradient(left, #ff512f, #dd2476);
  background-image: -webkit-linear-gradient(left, #ff512f, #dd2476);
  background-image: linear-gradient(to right, #ff512f, #dd2476);
  font-family: 'Roboto', sans-serif;
}

.contact-wrap {
  padding: 15px;
}

h1 {
  background-color: white;
  color: #ff7c62;
  padding: 40px;
  margin: 0 0 50px;
  font-size: 30px;
  text-transform: uppercase;
  font-weight: 700;
  text-align: center;
}
h1 small {
  font-size: 18px;
  display: block;
  text-transform: none;
  font-weight: 300;
  margin-top: 10px;
  color: #ff7c62;
}

.made-with-love {
  margin-top: 40px;
  padding: 10px;
  clear: left;
  text-align: center;
  font-size: 10px;
  font-family: arial;
  color: #fff;
}
.made-with-love i {
  font-style: normal;
  color: #F50057;
  font-size: 14px;
  position: relative;
  top: 2px;
}
.made-with-love a {
  color: #fff;
  text-decoration: none;
}
.made-with-love a:hover {
  text-decoration: underline;
}
</style>
<script type="text/javascript">
  var appurl = '{{url("/")}}/';
</script>
</head>
<body>

<h1> Scrap Data</h1>
<section class="contact-wrap">
  <form method="post" action="{{route('post-all-scrapping-url')}}" autocomplete="off" class="scrap-form">
    @csrf
    <div class="col-sm-6">
      <div class="input-block">
        <label for="">Select Category</label>
        <select name="category" required class="form-control" onchange="getSubCat(this.value)">
          <option value="0">--Select Category--</option>
          @foreach($categories as $cat)
            <option value="{{$cat->id}}">{{$cat->title}}</option>
          @endforeach
        </select>
      </div>
    </div>
    <div class="col-sm-6">
      <div class="input-block">
        <label for="">Select Sub Category</label>
        <select name="subcategory" id="subcategory" required class="form-control">
          <option value="0">--Select Sub Category--</option>
          @foreach($categories as $cat)
            <option value="{{$cat->id}}">{{$cat->title}}</option>
          @endforeach
        </select>
      </div>
    </div>
    <div class="col-sm-3">
      <div class="input-block">
        <label for="">VAT</label>
        <input type="text" name="vat" class="form-control" required>
      </div>
    </div>
    <div class="col-sm-9">
      <div class="input-block">
        <label for="">Enter order.se Category URL</label>
        <input type="text" name="url" class="form-control" required>
      </div>
    </div>
    <div class="col-sm-12">
      <button class="square-button">Submit</button>
      <br>
    </div>
  </form>
</section>
<div>
  <div class="col-md-12">
    <br>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th width="5%">#</th>
                <th>Category</th>
                <th>Sub Category</th>
                <th>Vat</th>
                <th>URL</th>
                <th>Read at</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $key => $value)
                <tr>
                    <td>{{$key + 1}}</td>
                    <td>{{$value->category}}</td>
                    <td>{{$value->subcategory}}</td>
                    <td>{{$value->vat}}</td>
                    <td>{{$value->url}}</td>
                    <td>{{$value->read_at}}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
  </div>
</div>

<script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script><script  src="./script.js"></script>
<script type="text/javascript">
  $('.scrap-form').find('.form-control').each(function() {
    var targetItem = $(this).parent();
    if ($(this).val()) {
      $(targetItem).find('label').css({
        'top': '10px',
        'fontSize': '14px'
      });
    }
  })
  $('.scrap-form').find('.form-control').focus(function() {
    $(this).parent('.input-block').addClass('focus');
    $(this).parent().find('label').animate({
      'top': '10px',
      'fontSize': '14px'
    }, 300);
  })
  $('.scrap-form').find('.form-control').blur(function() {
    if ($(this).val().length == 0) {
      $(this).parent('.input-block').removeClass('focus');
      $(this).parent().find('label').animate({
        'top': '25px',
        'fontSize': '18px'
      }, 300);
    }
  })

  function getSubCat(catId)
  {
    $.ajax({
      type: "GET",
      url: appurl+"sub-category-list/"+catId+"/1",
      data:'',
      success: function(data){
        $("#subcategory").html(data);
      }
  });
  }
</script>
</body>
</html>

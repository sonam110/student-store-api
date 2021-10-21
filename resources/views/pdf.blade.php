<!DOCTYPE HTML>

<html>
<head>
    <meta charset="UTF-8">
    <title>{{\mervick\aesEverywhere\AES256::decrypt($user->first_name, env('ENCRYPTION_KEY'))}} {{\mervick\aesEverywhere\AES256::decrypt($user->last_name, env('ENCRYPTION_KEY'))}}</title>
    <style type="text/css">
    body {
        font-family: "Adobe Caslon Pro", "Minion Pro", serif;
        font-size: 12pt;
    }
    .left-section{
        width:35%;
        margin-right:2%;
        float: left;
        background: #f5f5f5;
    }
    .right-section {
        width:65%;
        float: left;
        margin-left: 15px;
    }
    .clearfix{
        clear:both
    }
    header {
        font-family: "Trajan Pro", serif;
        padding-bottom: 10px;
    }

    header h1 {
        font-size: 20pt;
        letter-spacing: 2pt;
        border-bottom: 1px solid black;
        margin-bottom: 4px;
    }

    header h2 {
        font-size: 16pt;
        letter-spacing: 1pt;
        margin-bottom: 4px;
    }

    header span {
        font-size: 10pt;
        float: right;
    }

    section h2 {
        font-family: "Trajan Pro", serif;
        font-size: 14pt;
    }

    section p {
        margin-left: 40px;
    }

    section.coverletter {
        margin-top: 40px;
    }

    section.coverletter p {
        margin-left: 0px;
    }

    section ul {
        list-style-type: circle;
    }
    .justified {
        text-align: justify;
    }
    .jobtable {
        display: table;
        width: 100%;
        border-bottom: 1px solid #c9c9c9;
    }

    .edtable {
        display: table;
        width: 100%;
        padding-bottom: 15px;
    }

    .skillstable {
        display: table;
        width: 100%;
    }

    .table {
        display: table;
    }

    .tablerow {
        display: table-row;
    }

    .jobtitle {
        display: table-cell;
        font-style: italic;
    }

    .right {
        display: table-cell;
        text-align: right;
    }

    .cell {
        display: table-cell;
    }

    .onlinecell {
        font-style: italic;
        padding-right: 10px;
    }

    .urlcell {
        display: table-cell;
        letter-spacing: 1px;
    }

    .pagebreak {
        page-break-before: always;
    }
    .text-center {
        text-align: center;
    }
    .text-left {
        text-align: left;
    }
    .p-5 {
        padding: 5px;
    }
    table.personal-info tr th, table.personal-info tr td {
        text-align: left;
    }
    table.personal-info tr td {
        margin-bottom: 10px;
    }
    .header-bg {
        background: url("https://api.studentstore.se/assets/bg-resume.png");
        background-size: contain;
        background-position: top top;
        background-repeat: no-repeat;
    }
</style>
</head>

<body>

    <div class="left-section">
        <div class="text-center p-5">
            <header id="info" class="header-bg">
                <h2>
                    {{\mervick\aesEverywhere\AES256::decrypt($user->first_name, env('ENCRYPTION_KEY'))}} {{\mervick\aesEverywhere\AES256::decrypt($user->last_name, env('ENCRYPTION_KEY'))}}
                </h2>
                <img src="{{$user->profile_pic_thumb_path}}" width="75" height="75" style="border-radius: 50px; border: 2px solid white;" />
                <br>

                <hr> 
                <div class="edtable text-left">
                    <div class="tablerow  text-left">
                        <span class="jobtitle"><strong>
                            {{getLangByLabelGroups('common','name')}}
                        </strong></span>
                    </div>
                    <div class="tablerow  text-left">
                        <span class=" text-left">{{\mervick\aesEverywhere\AES256::decrypt($user->first_name, env('ENCRYPTION_KEY'))}} {{\mervick\aesEverywhere\AES256::decrypt($user->last_name, env('ENCRYPTION_KEY'))}}</span>
                    </div>
                </div>

                <div class="edtable text-left">
                    <div class="tablerow  text-left">
                        <span class="jobtitle"><strong>
                            {{getLangByLabelGroups('reward_point_share_screen','mobile')}}
                        </strong></span>
                    </div>
                    <div class="tablerow text-left">
                        <span class=" text-left">{{\mervick\aesEverywhere\AES256::decrypt($user->contact_number, env('ENCRYPTION_KEY'))}}</span>
                    </div>
                </div>

                <div class="edtable text-left">
                    <div class="tablerow text-left">
                        <span class="jobtitle"><strong>
                            {{getLangByLabelGroups('pdf','Email')}}
                        </strong></span>
                    </div>
                    <div class="tablerow text-left">
                        <span class=" text-left">{{\mervick\aesEverywhere\AES256::decrypt($user->email, env('ENCRYPTION_KEY'))}}</span>
                    </div>
                </div>

                <div class="edtable text-left">
                    <div class="tablerow  text-left">
                        <span class="jobtitle"><strong>
                            {{getLangByLabelGroups('pdf','Date_of_birth')}}
                        </strong></span>
                    </div>
                    <div class="tablerow  text-left">
                        <span class=" text-left">{{\Carbon\Carbon::parse(\mervick\aesEverywhere\AES256::decrypt($user->dob, env('ENCRYPTION_KEY')))->format('d M, Y')}}</span>
                    </div>
                </div>

                <div class="edtable text-left">
                    <div class="tablerow text-left">
                        <span class="jobtitle"><strong>
                            {{getLangByLabelGroups('pdf','Address')}}
                        </strong></span>
                    </div>
                    <div class="tablerow text-left">
                        <span class="text-left">{{$user->defaultAddress->country}},   
                            {{$user->defaultAddress->state}},
                            {{$user->defaultAddress->city}}</span>
                    </div>
                </div>

                <section id="skills">
                    <h3 class="text-left">{{getLangByLabelGroups('pdf','Languages_Known')}}</h3>
                    <div class="skillstable">
                        <div class="tablerow text-left">
                            <?php 
                            $languagesKnown = json_decode($user->userCvDetail->languages_known);
                            ?>
                            <ul class="cell">
                                @foreach($languagesKnown as $lang) 
                                <li class="text-left">{{$lang}}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </section>
            </header>

        </div>
    </div>
    <div class="right-section">
        <section>
            <div class="justified">{{$user->userCvDetail->other_description}}</div>
        </section>

        <section>
            <h2>{{getLangByLabelGroups('pdf','Education')}}</h2>
            <ul>
                @forelse($user->userEducationDetails as $education)
                <li>
                    <strong>
                        {{$education->title}}
                    </strong> <br>
                    {{$education->country}} <br>
                    {{\Carbon\Carbon::parse($education->from_date)->format('M Y')}} - 
                    @if(!empty($education->to_date)) {{\Carbon\Carbon::parse($education->to_date)->format('M Y')}} @else {{getLangByLabelGroups('education_and_training','ongoing')}} @endif
                </li>
                @empty
                @endforelse
            </ul>
        </section>

        <div class="clearfix"></div>

        <section id="employment">
            <h2>{{getLangByLabelGroups('pdf','Work_Experience')}}</h2>
            @forelse($user->userWorkExperiences as $key => $experiences)
            @if($key>0) <br> @endif
            <section>
                <div class="jobtable">
                    <div class="tablerow">
                        <span class="jobtitle"><strong>{{$experiences->title}}</strong></span>
                        <span class="right">{{\Carbon\Carbon::parse($experiences->from_date)->format('M Y')}} - @if(!empty($experiences->to_date)) {{\Carbon\Carbon::parse($experiences->to_date)->format('M Y')}} @else {{getLangByLabelGroups('education_and_training','ongoing')}} @endif</span>
                    </div>
                    <div class="tablerow">
                        <span>{{$experiences->employer_name}}, {{$experiences->country}}, {{$experiences->city}}</span>
                    </div>
                    <ul>
                        <li>{{$experiences->activities_and_responsibilities}}</li>
                    </ul>
                </div>
            </section>
            @empty
            @endforelse
        </section>

        <section>
            <h2>{{getLangByLabelGroups('job_environment','title')}}</h2>
            <ul>
                <?php 
                    $preferred_job_env = json_decode($user->userCvDetail->preferred_job_env);
                ?>
                @forelse($preferred_job_env as $env)
                <li>{{getLangByLabelGroups('job_environment', $env)}}</li>
                @empty
                @endforelse
            </ul>
        </section>

        <div class="clearfix"></div>

        <!-- <div class="pagebreak"></div> -->

        <section id="skills">
            <h2>{{getLangByLabelGroups('pdf','Key_Skills')}}</h2>
            <div class="skillstable">
                <div class="tablerow">
                    <ul class="cell">
                        <?php 
                            $keySkills = json_decode($user->userCvDetail->key_skills);
                        ?>
                        @forelse($keySkills as $keySkill)
                        <li>{{$keySkill}}</li>
                        @empty
                        @endforelse
                    </ul>
                </div>
            </div>
        </section>
    </div>
    <div class="clearfix"></div>
    
</body>
</html>
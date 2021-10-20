<!DOCTYPE html>
<html lang="en" >
<head>
    <meta charset="UTF-8">
    <title>Resume Data</title>
    <style type="text/css">

        .primaryContent{
            display:flow-root;
        }
        .mainDetails {
            padding: 25px 35px 5px;
            border-bottom: 2px solid #cf8a05;
            background: #f3f3f3;
        }

        .bioDetails{
            margin-top:10px;
            font-size:.75em;
        }

        #name h1 {
            font-size: 2em;
            font-weight: 700;
            margin-bottom: -6px;
            text-align: center;
        }

        .item{
            height:1em;
        }


        #headshot {
            width: 12.5%;
            float: left;
            margin-right: 30px;
        }

        #headshot img {
            width: 100%;
            height: auto;
            border-radius: 50px;
        }

        #contactDetails ul {
            list-style-type: none;
            font-size: 0.9em;
            margin-top: 2px;
        }

        #contactDetails ul li {
            margin-bottom: 3px;
            color: #444;
            display: inline;
        }

        #personalArea section:first-child {
            border-top: 0;
        }
        .sectionTitle h1 {

            font-size: 0.89em;
            color: #cf8a05;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .sectionContent h2 {
            font-size: 1.5em;
            margin-bottom: 0px;
        }

        .subDetails {
            font-size: 0.8em;
            font-style: bold;
            margin-bottom: 15px;
        }

        .keySkills {
            -moz-column-count: 3;
            column-count: 3;
            font-size: 1em;
            color: #444;
        }

        .keySkills ul li {
            margin-bottom: 3px;
        }
        .sectionTitle {
            float: left;
            width: 100%;
            margin-bottom:0em;
            font-size:1.5em;
            margin-top: 25px;
        }

        .sectionContent {
            float: right;
            width: 100%;
        }
        p{
            margin-top: 0px;
        }
        table th {
            text-align: left;
        }
    </style>
</head>
<body>
    <body>
        <div id="drag" class="cv instaFade breakFastBurrito" style="padding: 0px">
            <div class="mainDetails">
                <div id="headshot" class="">
                    <img src="{{$user->profile_pic_thumb_path}}" />
                </div>
                <div id="name">
                    <h1 class=" delayTwo">{{\mervick\aesEverywhere\AES256::decrypt($user->first_name, env('ENCRYPTION_KEY'))}} {{\mervick\aesEverywhere\AES256::decrypt($user->last_name, env('ENCRYPTION_KEY'))}}</h1>
                </div>
                <div class="bioDetails">
                    <div style="text-align: center;">{{\Carbon\Carbon::parse(\mervick\aesEverywhere\AES256::decrypt($user->dob, env('ENCRYPTION_KEY')))->format('M d, Y')}}</div>
                    @if($user->userCvDetail)
                    @if($user->userCvDetail->addressDetail)
                    <div style="text-align: center;">Citizenship: {{$user->userCvDetail->addressDetail->country}}</div>
                    <div style="text-align: center;">{{$user->userCvDetail->addressDetail->city}}</div>
                    @endif
                    @endif
                </div>
                <div class="clear"></div>
            </div>
            <div class="primaryContent">
                <div id="mainArea" class=" delayFive">
                    <section id="Profile">
                        <article>
                            <div class="sectionTitle">
                                <h1>{{getLangByLabelGroups('pdf','Career_Objective')}}</h1>
                            </div>
                            <div class="sectionContent">
                                <p>{{$user->userCvDetail->other_description}} </p>
                            </div>
                        </article>
                        <div class="clear"></div>
                    </section>
                    <section id="Profile">
                        <article>
                            <div class="sectionTitle">
                                <h1>{{getLangByLabelGroups('job_environment','title')}}</h1>
                            </div>
                            <div class="sectionContent">
                                <ul class="keySkills">
                                    <?php 
                                        $preferred_job_env = json_decode($user->userCvDetail->preferred_job_env);
                                    ?>
                                    @forelse($preferred_job_env as $env)
                                    <li>{{getLangByLabelGroups('job_environment', $env)}}</li>
                                    @empty
                                    @endforelse
                                </ul>
                            </div>
                        </article>
                        <div class="clear"></div>
                    </section>
                    <!-- <section id="credentials">
                        <div class="sectionTitle">
                            <h1>Ministerial Credentials</h1>
                        </div>
                        <div class="sectionContent">
                            <article>
                                <strong>Ordained to the Presbyterate</strong>
                                <p>Ordained by Kenneth Ross, Bishop of the Diocese of the Rocky Mountains. Ordained by Kenneth Ross, Bishop of the Diocese of the Rocky Mountains. </p>
                            </article>
                            <article>
                                <strong>Ordained to the Presbyterate</strong>
                                <p>Ordained by Kenneth Ross, Bishop of the Diocese of the Rocky Mountains. Ordained by Kenneth Ross, Bishop of the Diocese of the Rocky Mountains. </p>
                            </article>
                            <article>
                                <strong>Ordained to the Presbyterate</strong>
                                <p>Ordained by Kenneth Ross, Bishop of the Diocese of the Rocky Mountains. Ordained by Kenneth Ross, Bishop of the Diocese of the Rocky Mountains. </p>
                            </article>
                        </div>
                        <div class="clear"></div>
                    </section> -->
                    <section id="Education">
                        <div class="sectionTitle">
                            <h1>{{getLangByLabelGroups('pdf','Education')}}</h1>
                        </div>
                        <div class="sectionContent">
                            <ul>
                                @forelse($user->userEducationDetails as $education)
                                <li>{{$education->title}} from {{$education->country}} started at {{\Carbon\Carbon::parse($education->from_date)->format('M Y')}} and end at {{\Carbon\Carbon::parse($education->to_date)->format('M Y')}}.</li>
                                @empty
                                @endforelse
                                <!-- <li>BCA from Swami Vivekanand Subharti University with 70% in 2016.</li>
                                <li>NIELIT A Level from UPTEC Computer Consultancy with A Grade.</li> -->
                            </ul>
                        </div>
                        <div class="clear"></div>
                    </section>
                    <section id="Work">
                        <div class="sectionTitle">
                            <h1>{{getLangByLabelGroups('pdf','Work_Experience')}}</h1>
                        </div>
                        <div class="sectionContent">
                            @forelse($user->userWorkExperiences as $experiences)
                            <article>
                                <strong>{{$experiences->title}}</strong>
                                <p style="margin-bottom: 0px">{{$experiences->employer_name}}, {{$experiences->country}}, {{$experiences->city}}</p>
                                <p class="subDetails" style="margin-bottom: 5px">{{\Carbon\Carbon::parse($experiences->from_date)->format('M Y')}} - {{\Carbon\Carbon::parse($experiences->to_date)->format('M Y')}}</p>
                                <p>{{$experiences->activities_and_responsibilities}}</p>
                            </article>
                            @empty
                            @endforelse
                            <!-- <article>
                                <strong>Field Technician</strong>
                                <p style="margin-bottom: 0px">All Phase Restoration, Windsor, CO, U.S.A.</p>
                                <p class="subDetails">Summer 2007, September 2008 - July 2009</p>
                                <p>Worked to mitigate the effects of water and fire damage in commercial and residential units. Suggested and developed an electronic paperwork system resulting in increased efficiency.</p>
                            </article> -->
                        </div>
                        <div class="clear"></div>
                    </section>
                    <section>
                        <div class="sectionTitle">
                            <h1>{{getLangByLabelGroups('pdf','Key_Skills')}}</h1>
                        </div>
                        <div class="sectionContent">
                            <ul class="keySkills">
                                <?php 
                                    $keySkills = json_decode($user->userCvDetail->key_skills);
                                    $languagesKnown = json_decode($user->userCvDetail->languages_known);
                                ?>
                                @forelse($keySkills as $keySkill)
                                <li>{{$keySkill}}</li>
                                @empty
                                @endforelse
                            </ul>
                        </div>
                        <div class="clear"></div>
                    </section>

                    

                    <section>
                        <div class="sectionTitle">
                            <h1>{{getLangByLabelGroups('pdf','Personal_Details')}}</h1>
                        </div>
                        <div class="sectionContent">
                            <table>
                                <tr>
                                    <th>{{getLangByLabelGroups('pdf','Contact')}}</th>
                                    <td>: {{\mervick\aesEverywhere\AES256::decrypt($user->contact_number, env('ENCRYPTION_KEY'))}}</td>
                                </tr>
                                <tr>
                                    <th>{{getLangByLabelGroups('pdf','Email')}}</th>
                                    <td>: {{\mervick\aesEverywhere\AES256::decrypt($user->email, env('ENCRYPTION_KEY'))}}</td>
                                </tr>
                                <tr>
                                    <th>{{getLangByLabelGroups('pdf','Address')}}</th>
                                    <td>: {{$user->defaultAddress->country}},   
                                        {{$user->defaultAddress->state}},
                                        {{$user->defaultAddress->city}}</td>
                                </tr>
                                <tr>
                                    <th>{{getLangByLabelGroups('pdf','Languages_Known')}}</th>
                                    <td>: {{implode(',',$languagesKnown)}}</td>
                                </tr>
                                <tr>
                                    <th>{{getLangByLabelGroups('pdf','Date_of_birth')}}</th>
                                    <td>: {{\Carbon\Carbon::parse(\mervick\aesEverywhere\AES256::decrypt($user->dob, env('ENCRYPTION_KEY')))->format('d M, Y')}}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="clear"></div>
                    </section>
                </div>
            </div>
        </div>
    </body>
</body>
</html>
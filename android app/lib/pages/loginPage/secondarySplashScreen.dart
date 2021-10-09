import 'dart:async';

import 'package:bkrm/pages/Nav2App.dart';
import 'package:bkrm/pages/loginPage/loginPage.dart';
import 'package:bkrm/services/info/managementInfo/userInfo.dart';
import 'package:bkrm/services/services.dart';
import 'package:flutter/material.dart';
import 'package:page_transition/page_transition.dart';

class SecondarySplashScreen extends StatefulWidget {
  @override
  _SecondarySplashScreenState createState() => _SecondarySplashScreenState();
}

class _SecondarySplashScreenState extends State<SecondarySplashScreen> {
  AssetImage bachgroundImage = AssetImage("asset/SPLASH_SCREEN_CUSTOM.png");
  @override
  void initState() {
    super.initState();
    prepare().then((value) {
      Future.delayed(Duration(seconds: 1),(){
        Navigator.pushAndRemoveUntil(context, PageTransition(type: PageTransitionType.fade, child: LoginPage(lastLoggedIn: value ,)),(route)=>false);
      });
    });
  }
  Future<UserInfo?> prepare() async {
    await BkrmService().networkAvailableCheck();
    debugPrint("Begin get last log in user");
    UserInfo? lastLogedIn = await BkrmService().getLastLogInUser();
    if (lastLogedIn != null) {
        return lastLogedIn;
    }
    return null;
  }

  @override
  Widget build(BuildContext context) {
    return Stack(
      children: [Container(
        decoration: BoxDecoration(
          image: DecorationImage(image: bachgroundImage,fit: BoxFit.cover)
        ),
      ),Positioned(child: Container(alignment: Alignment.center,height:100,width: MediaQuery.of(context).size.width,child: Container(width: 200,child: Center(child: LinearProgressIndicator(minHeight: 20,),))),bottom: 50)
]
    );
  }


}

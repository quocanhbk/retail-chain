import 'package:bkrm/services/services.dart';
import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';

import "package:bkrm/pages/Nav2App.dart";

import 'package:bkrm/pages/loginPage/Widget/bezierContainer.dart';
import 'package:flutter/services.dart';

class SelectModulePage extends StatefulWidget {
  @override
  _SelectModulePageState createState() => _SelectModulePageState();
}

class _SelectModulePageState extends State<SelectModulePage> {
  List<Widget> listModuleAccessible = [];

  @override
  void initState() {
    if(BkrmService().currentUser!.roles.contains("selling")){
      listModuleAccessible.add(SizedBox(height: 20,));
      listModuleAccessible.add(ElevatedButton(onPressed: (){
        Navigator.pushNamed(context, Nav2App.shoppingCartRoute);
      }, child: Container(
        alignment: Alignment.center,
        width: 300,
        height: 80,
        child: Text("Bán hàng",style: TextStyle(fontSize: 30,fontFamily: "PlayfairDisplay"),textAlign: TextAlign.center,),
      )));
    }
    if(BkrmService().currentUser!.roles.contains("purchasing")){
      listModuleAccessible.add(SizedBox(height: 20,));
      listModuleAccessible.add(ElevatedButton(onPressed: BkrmService().networkAvailable?(){
        Navigator.pushNamed(context, Nav2App.inventoryRoute);
      }:null, child: Container(
        alignment: Alignment.center,
        width: 300,
        height: 80,
        child: Text("Kho hàng",style: TextStyle(fontSize: 30,fontFamily: "PlayfairDisplay"),textAlign: TextAlign.center,),
      )));
    }
    if(BkrmService().currentUser!.roles.contains("managing")){
      listModuleAccessible.add(SizedBox(height: 20,));
      listModuleAccessible.add(ElevatedButton(onPressed: BkrmService().networkAvailable?(){
        Navigator.pushNamed(context, Nav2App.listShiftRoute);
      }:null, child: Container(
        alignment: Alignment.center,
        width: 300,
        height: 80,
        child: Text("Nhân sự",style: TextStyle(fontSize: 30,fontFamily: "PlayfairDisplay"),textAlign: TextAlign.center,),
      )));
    }
    if(BkrmService().currentUser!.roles.contains("reporting")){
      listModuleAccessible.add(SizedBox(height: 20,));
      listModuleAccessible.add(ElevatedButton(onPressed: BkrmService().networkAvailable?(){
        Navigator.pushNamed(context, Nav2App.dashboardRoute);
      }:null, child: Container(
        alignment: Alignment.center,
        width: 300,
        height: 80,
        child: Text("Quản lý",style: TextStyle(fontSize: 30,fontFamily: "PlayfairDisplay"),textAlign: TextAlign.center,),
      )));
    }
    super.initState();
  }

  @override
  Widget build(BuildContext context) {
    return WillPopScope(
      onWillPop: ()async{
        showDialog(context: context, builder: (context){
          return AlertDialog(
            title: Text("Bạn có chắc muốn thoát ứng dụng?"),
            actions: [
              TextButton(onPressed: (){
                Navigator.pop(context);
              }, child: Text("Không")),
              TextButton(onPressed: (){
                Navigator.pop(context);
                SystemChannels.platform.invokeMethod('SystemNavigator.pop');
              }, child: Text("Có")),
            ],
          );
        });
        return true;
      },
      child: Scaffold(
        body: Stack(
          children: [
            Positioned(
                top: -MediaQuery.of(context).size.height * .15,
                right: -MediaQuery.of(context).size.width * .4,
                child: BezierContainer()),
            SingleChildScrollView(
              padding: EdgeInsets.all(10.0),
            child: Column(
              children: [
                SizedBox(height: 50,),
                Row(
                  children: [
                    Expanded(
                      flex:1,
                      child: Container(
                        alignment: Alignment.centerLeft,
                        child: IconButton(icon: Icon(Icons.power_settings_new,color:Colors.blue,size: 40,),onPressed: ()async{
                          showDialog(context: context, builder: (context){
                            return AlertDialog(
                              title: Text("Bạn có chắc muốn thoát ứng dụng?"),
                              actions: [
                                TextButton(onPressed: (){
                                  Navigator.pop(context);
                                }, child: Text("Không")),
                                TextButton(onPressed: (){
                                  Navigator.pop(context);
                                  SystemChannels.platform.invokeMethod('SystemNavigator.pop');
                                }, child: Text("Có")),
                              ],
                            );
                          });

                        },),
                      ),
                    ),
                    Expanded(
                      flex: 1,
                      child: Container(
                      alignment: Alignment.centerRight,
                      child: IconButton(
                        icon: Icon(Icons.logout,size: 40,color: Colors.brown,),
                        onPressed: (){
                          showDialog(context: context, builder: (context){
                            return AlertDialog(
                              title: Text("Xác nhận đăng xuất ?"),
                              actions: [
                                TextButton(onPressed: (){
                                  Navigator.pop(context);
                                }, child: Text("Không")),
                                TextButton(onPressed: ()async{
                                  showDialog(context: context, builder: (context){
                                    return AlertDialog(
                                      title: Text("Đang đăng xuất!"),
                                      content: Container(
                                        height: 50,
                                        child: Center(
                                          child: CircularProgressIndicator(),
                                        ),
                                      ),
                                    );
                                  });
                                  await BkrmService().logOut();
                                  Navigator.pushNamedAndRemoveUntil(
                                      context, Nav2App.welcomePage, (route) => false);
                                }, child: Text("Xác nhận"))
                              ],
                            );
                          });
                        },
                      ),
                  ),
                    ),]
                ),
                SizedBox(
                  height: 30,
                ),
                Center(
                  child: Text("Chào mừng "+BkrmService().currentUser!.name+" đến với BKRM!",style: TextStyle(fontSize: 30,fontWeight: FontWeight.bold,fontFamily: "PlayfairDisplay"),textAlign: TextAlign.center,),
                ),
                BkrmService().networkAvailable?Container():Center(child: Text("(Bạn đang ở chế độ offline) ",style: TextStyle(fontSize: 20,fontWeight: FontWeight.bold,fontFamily: "PlayfairDisplay"),)),
                SizedBox(height: 80),
                Container(
                  alignment: Alignment.centerLeft,
                  child: Text("Hãy chọn trang chức năng bạn muốn vào: ",style: TextStyle(fontSize: 20,fontWeight: FontWeight.bold,fontFamily: "PlayfairDisplay"),),
                ),
                SizedBox(
                  height: 10,
                ),
                Column(
                  children: listModuleAccessible,
                )
              ],
            ),
          ),]
        ),
      ),
    );
  }
}

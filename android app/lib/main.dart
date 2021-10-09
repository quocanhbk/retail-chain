
import 'package:bkrm/pages/Nav2App.dart';
import 'package:bkrm/pages/loginPage/welcomePage.dart';
import 'package:bkrm/pages/managementModule/detailReport/listItemNoPurchasePrice.dart';
import 'package:bkrm/services/info/inventoryInfo/itemInfo.dart';
import 'package:bkrm/services/services.dart';
import 'package:flutter/material.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:page_transition/page_transition.dart';
final FlutterLocalNotificationsPlugin flutterLocalNotificationsPlugin =
FlutterLocalNotificationsPlugin();

void main()async{
  WidgetsFlutterBinding.ensureInitialized();
  const AndroidInitializationSettings initializationSettingsAndroid =
  AndroidInitializationSettings('app_icon');
  final InitializationSettings initializationSettings = InitializationSettings(
      android: initializationSettingsAndroid,
      iOS: null,
      macOS: null);
  await flutterLocalNotificationsPlugin.initialize(initializationSettings,
      onSelectNotification: selectNotification);
  runApp(Nav2App());
}

Future selectNotification(String? payload) async {
  if(payload=="have_items_with_no_purchase_price"){
    showDialog(context: navigatorKey.currentContext!,builder:(context){
      return AlertDialog(
        content: Container(
          height: 50,
          child: Center(
            child: CircularProgressIndicator(),
          ),
        ),
      );
    });
    Navigator.pop(navigatorKey.currentContext!);
    List<ItemInfo> itemsWithNoPurchasePrice = await BkrmService().getItemsWithNoPurchasePrice();
    Navigator.push(navigatorKey.currentContext!, PageTransition(child: ListItemNoPurchasePrice(itemsWithNoPurchasePrice), type: pageTransitionType));
  }
}


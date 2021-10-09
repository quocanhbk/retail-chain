import 'dart:async';
import 'dart:io';

import 'package:bkrm/pages/Nav2App.dart';
import 'package:bkrm/services/api.dart';
import 'package:bkrm/services/info/inventoryInfo/quantityHistory.dart';
import 'package:bkrm/services/info/inventoryInfo/returnPurchasedSheetInfo.dart';
import 'package:bkrm/services/info/inventoryInfo/returnPurchasedSheetPagination.dart';
import 'package:bkrm/services/info/managementInfo/historyInfo.dart';
import 'package:flutter/cupertino.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'info/hrInfo/attendanceInfo.dart';
import 'package:bkrm/services/info/report/chartInfo.dart';
import 'info/inventoryInfo/priceHistory.dart';
import 'info/sellingInfo/invoicePagination.dart';
import 'info/inventoryInfo/itemPagination.dart';
import 'package:bkrm/services/info/inventoryInfo/purchasedSheetPagination.dart';
import 'info/sellingInfo/refundPagination.dart';
import 'info/hrInfo/scheduleInfo.dart';
import 'info/inventoryInfo/categoryInfo.dart';
import 'package:bkrm/services/info/inventoryInfo/defaultItemInfo.dart';
import 'package:bkrm/services/info/hrInfo/employeeInfo.dart';
import 'package:bkrm/services/info/inventoryInfo/itemInfo.dart';
import 'info/sellingInfo/refundInfo.dart';
import 'package:bkrm/services/info/hrInfo/shiftInfo.dart';
import 'package:bkrm/services/info/inventoryInfo/supplierInfo.dart';
import 'package:bkrm/services/info/managementInfo/userInfo.dart';
import 'package:connectivity_plus/connectivity_plus.dart';
import 'package:dio/dio.dart';
import 'package:bkrm/services/info/sellingInfo/customerInfo.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:path_provider/path_provider.dart';
import 'package:sembast/sembast.dart';
import 'package:sembast/sembast_io.dart';

import 'cartService.dart';
import 'importGoodService.dart';
import 'info/managementInfo/dashboardInfo.dart';
import 'info/invoice/invoiceReceivedWhenGet.dart';
import 'info/inventoryInfo/purchasedSheetInfo.dart';
import 'package:bkrm/main.dart';
import 'package:bkrm/services/info/report/categoryReportInfo.dart' as categoryReport;
import 'package:bkrm/services/info/report/itemReportInfo.dart' as itemReport;
import 'package:bkrm/services/info/report/customerReportInfo.dart' as customerReport;
import 'package:bkrm/services/info/report/supplierReportInfo.dart' as supplierReport;

enum MsgInfoCode {
  actionSuccess,
  actionFail,
  signUpSuccess,
  signUpFail,
  usernameAlreadyBeenTaken,
  phoneNumberAlreadyBeenTaken,
  logInSucess,
  logInFail,
  wrongPasswordOrUsername,
  serverNotAvailable,
  alreadyHaveUserLogedIn,
  logOutSuccess,
  inputIsNull,
  alreadyWorkInShift
}

class BkrmService {
  static final BkrmService _instance = BkrmService._internal();
  ApiService api = ApiService();
  CartService? _cart;
  List<CartService> listCart = [CartService()];
  ImportGoodService? _importGood = ImportGoodService();
  UserInfo? _currentUser;
  Database? _noSqlDb;
  StoreRef? _noSqlStore;
  Timer? timer;
  bool prevNetworkState = true;
  bool networkAvailable = true;

  UserInfo? get currentUser{
    return this._currentUser;
  }


  set currentUser(UserInfo? value) {
    _currentUser = value;
    if(value!=null){
      initDb(userName: value.username);
    }
  }

  List<ItemInfo> storedItemInfo = [];
  List<ItemInfo> storedItemPageItemNameAsc = [];
  List<ItemInfo> storedItemPageItemNameDesc = [];
  List<ItemInfo> storedItemPageSellPriceAsc = [];
  List<ItemInfo> storedItemPageSellPriceDesc = [];
  List<ItemInfo> storedItemPageCreatedDateAsc = [];
  List<ItemInfo> storedItemPageCreatedDateDesc = [];
  List<CustomerInfo> storedCustomerInfo = [];
  List<CategoryInfo> storedCategoryInfo = [];

  Future<bool> networkAvailableCheck() async {
    var connectivityResult = await Connectivity().checkConnectivity();
    if (connectivityResult == ConnectivityResult.none) {
      prevNetworkState = networkAvailable;
      networkAvailable = false;
    } else {
      prevNetworkState = networkAvailable;
      networkAvailable = true;
    }
    return networkAvailable;
  }

  Database? get db {
    if (_noSqlDb == null) {
      if(currentUser!=null){
        initDb(userName: currentUser!.username);
      }
      else{
        initDb();
      }
    }
    return _noSqlDb;
  }

  StoreRef? get store {
    if (_noSqlStore == null) {
      initDb();
    }
    return _noSqlStore;
  }

  ImportGoodService? get importGood {
    if (_importGood == null) {
      _importGood = ImportGoodService();
    }
    return _importGood;
  }

  CartService? get cart {
    if (_cart == null) {
      if (listCart.isEmpty) {
        listCart.add(CartService());
      }
      _cart = listCart.first;
    }
    return _cart;
  }

  set cart(CartService? value) {
    if (value == _cart) {
      return;
    }
    _cart = value;
    requestCart();
  }

  factory BkrmService() {
    return _instance;
  }

  BkrmService._internal() {
    getLastLogInUser().then((value){
      if(value!=null){
        this.currentUser=value;
      }
    });
  }
  StreamController _cartServiceController = StreamController.broadcast();
  Stream get cartServiceStream => _cartServiceController.stream;

  void requestCart() {
    _cartServiceController.sink.add({
      "totalPrice": cart!.totalPrice,
      "totalDiscount": cart!.totalDiscount,
      "totalDiscountPrice": cart!.totalDiscountPrice,
      "listCartItem": cart!.cartItems.reversed.toList(),
      "valid": cart!.valid,
      "usedCustomerPoint": cart!.usedCustomerPoint
    });
  }

  void dispose() {
    _cartServiceController.close();
  }

  ///
  /// Local db
  ///

  initDb({String? userName}) async {
    String dbPath =
        (await getApplicationDocumentsDirectory()).path + "/localDb.db";
    DatabaseFactory dbFactory = databaseFactoryIo;
    this._noSqlDb = await dbFactory.openDatabase(dbPath);
    if (this.currentUser == null) {
      if (userName != null) {
        this._noSqlStore = intMapStoreFactory.store(userName);
      } else {
        this._noSqlStore = StoreRef.main();
      }
    } else {
      this._noSqlStore = intMapStoreFactory.store(this.currentUser!.username);
    }
  }

  Future<UserInfo?> getLastLogInUser()async{
    await initDb();
    var store = StoreRef.main();
    var result = await store.record(1).get(db!);
    if(result==null){
      return null;
    }else{
      String? userName = result["userName"];
      if(userName==null){
        return null;
      }
      var userStore = intMapStoreFactory.store(userName);
      Map<String,dynamic>? returnUsers = await userStore.record(3).get(db!);
      if(returnUsers!=null){
          UserInfo lastLoggedInUser = UserInfo.fromJson(returnUsers);
          return lastLoggedInUser;
      }else{
        return null;
      }
    }
  }

  Future<bool> storeUserLoggedIn(UserInfo userInfo)async{
    await initDb(userName:this.currentUser!.username);
    var store = StoreRef.main();
    debugPrint("storeCurrentUserLoggedIn");
    debugPrint(userInfo.toString());
    await store.record(1).put(db!, {"userName":userInfo.username,"userId":userInfo.userId,"stayLoggedIn":userInfo.stayLoggedIn});
    return true;
  }

  Future<bool> clearLastUserLoggedIn()async{
    await initDb();
    var store = StoreRef.main();
    await store.record(1).put(db!, {});
    return true;
  }

  Future<bool> addDeliverNameToSupplier(
      String deliverName, SupplierInfo? supplier) async {
    String phoneNumber;
    if (supplier == null) {
      phoneNumber = "-1";
    } else {
      phoneNumber = supplier.phoneNumber!;
    }
    var result = await this.store!.record(1).get(db!);
    if (result == null) {
      await this.store!.record(1).put(db!, {
        phoneNumber: <String>[deliverName]
      });
    } else {
      Map<String, dynamic> listSupplier = result as Map<String, dynamic>;
      if (listSupplier[phoneNumber] == null) {
        Map<String, dynamic> cloneMapSupplier = Map.from(listSupplier);
        cloneMapSupplier[phoneNumber] = [deliverName];
        await this.store!.record(1).put(db!, cloneMapSupplier);
      } else {
        List<String> convertResult = List.from(listSupplier[phoneNumber]!);
        if (!convertResult.contains(deliverName)) {
          convertResult.add(deliverName);
          Map<String, dynamic> cloneMapSupplier = Map.from(listSupplier);
          cloneMapSupplier[phoneNumber] = convertResult;
          await this.store!.record(1).put(db!, cloneMapSupplier);
        } else {
          return true;
        }
      }
    }
    return true;
  }

  Future<List<String>> getDeliverNameOfSupplier(SupplierInfo? supplier) async {
    String phoneNumber;
    if (supplier == null) {
      phoneNumber = "-1";
    } else {
      phoneNumber = supplier.phoneNumber!;
    }
    var result = await this.store!.record(1).get(db!);
    if (result == null) {
      return [];
    } else {
      Map<String, dynamic> listSupplier = result as Map<String, dynamic>;
      if (listSupplier[phoneNumber] != null) {
        return List<String>.from(listSupplier[phoneNumber]);
      } else {
        return [];
      }
    }
  }

  Future<bool> storeDataForThisSession() async {
    List<Map<String, dynamic>> listItemJson =
        storedItemInfo.map((e) => e.toJson()).toList();
    List<Map<String, dynamic>> listCustomerJson =
        storedCustomerInfo.map((e) => e.toJson()).toList();
    List<Map<String, dynamic>> listCategoryJson =
        storedCategoryInfo.map((e) => e.toJson()).toList();
    Map<String, dynamic> currentUser = this.currentUser!.toJson();
    await this.store!.record(3).put(db!, currentUser);
    await this.store!.record(4).put(db!, listItemJson);
    await this.store!.record(5).put(db!, listCustomerJson);
    await this.store!.record(6).put(db!, listCategoryJson);
    return true;
  }

  Future<bool> getDataFromLastSessionForThisSession() async {
    var currentUser = await this.store!.record(3).get(db!);
    if (currentUser != null) {
      this.currentUser = UserInfo.fromJson(currentUser);
    } else {
      return false;
    }
    var listItemInfoJson = await this.store!.record(4).get(db!);
    if (listItemInfoJson != null) {
      List<Map<String, dynamic>> temp = List.from(listItemInfoJson);
      debugPrint("Item info :" + temp.toString());
      for (Map<String, dynamic> item in temp) {
        storedItemInfo.add(ItemInfo.fromJson(item));
      }
    } else {
      this.currentUser = null;
      return false;
    }
    var listCustomerInfoJson = await this.store!.record(4).get(db!);
    if (listCustomerInfoJson != null) {
      List<Map<String, dynamic>> temp = List.from(listCustomerInfoJson);
      debugPrint("Customer info :" + temp.toString());
      for (Map<String, dynamic> customer in temp) {
        storedCustomerInfo.add(CustomerInfo.fromJson(customer));
      }
    } else {
      this.currentUser = null;
      this.storedItemInfo.clear();
      return false;
    }
    var listCategoryInfoJson = await this.store!.record(4).get(db!);
    if (listCategoryInfoJson != null) {
      List<Map<String, dynamic>> temp = List.from(listCategoryInfoJson);
      debugPrint("Category info :" + temp.toString());
      for (Map<String, dynamic> category in temp) {
        storedCategoryInfo.add(CategoryInfo.fromJson(category));
      }
    } else {
      this.currentUser = null;
      this.storedItemInfo.clear();
      this.storedCustomerInfo.clear();
      return false;
    }
    if(storedCategoryInfo.isEmpty){
      return false;
    }
    return true;
  }

  Future<bool> addRequestToQueue(
      String requestPath, Map<String, dynamic> data) async {
    debugPrint(
        "Add request path " + requestPath + " and data " + data.toString());
    var result = await this.store!.record(2).get(db!);
    if (result == null) {
      await this.store!.record(2).put(db!, <Map<String, dynamic>>[
        {"path": requestPath, "data": data}
      ]);
      return true;
    } else {
      List<Map<String, dynamic>> listRequest = List.from(result);
      listRequest.add({"path": requestPath, "data": data});
      await this.store!.record(2).put(db!, listRequest);
      return true;
    }
  }

  Future<bool> addAllRequestToQueue(
      List<Map<String, dynamic>> listQueuedRequest) async {
    for (var request in listQueuedRequest) {
      await addRequestToQueue(request["path"], request["data"]);
    }
    return true;
  }

  Future<List<Map<String, dynamic>>> getQueuedRequest() async {
    debugPrint("Get queue Request");
    var result = await this.store!.record(2).get(db!);
    if (result == null) {
      return [];
    } else {
      List<Map<String, dynamic>> listRequest = List.from(result);
      debugPrint(listRequest.toString());
      return listRequest;
    }
  }

  Future<bool> removeAllQueuedRequest() async {
    await this.store!.record(2).put(db!, <Map<String, dynamic>>[]);
    return true;
  }

  Future<UserInfo?> getLastSessionUser() async {
    var currentUser = await this.store!.record(3).get(db!);
    if (currentUser != null) {
      debugPrint("Get last user form local db: " +
          UserInfo.fromJson(currentUser).toJson().toString());
      return UserInfo.fromJson(currentUser);
    } else {
      return null;
    }
  }

  Future<bool> checkToChangeToOfflineMode() async {
    if (!(await BkrmService().networkAvailableCheck())) {
      if (prevNetworkState) {
        if (navigatorKey.currentContext != null) {
          showDialog(
              context: navigatorKey.currentContext!,
              builder: (context) {
                return AlertDialog(
                  title: Text("Thông báo"),
                  content: Container(
                    child: Column(mainAxisSize: MainAxisSize.min, children: [
                      Text(
                        "Không có tín hiệu kết nối mạng. Sẽ chuyển sang chế độ offline...",
                        style: TextStyle(
                            fontSize: 16, fontFamily: "PlayfairDisplay"),
                      ),
                      Container(
                        height: 50,
                        child: Center(
                          child: CircularProgressIndicator(),
                        ),
                      )
                    ]),
                  ),
                );
              });
          await Future.delayed(Duration(seconds: 2));
          Navigator.pushNamedAndRemoveUntil(navigatorKey.currentContext!,
              Nav2App.selectModuleroute, (route) => false);
        }
        return true;
      }
    } else {
      if (prevNetworkState == false) {
        if (navigatorKey.currentContext != null) {
          if(this.currentUser!.token==""){
            showDialog(
                context: navigatorKey.currentContext!,
                builder: (context) {
                  return AlertDialog(
                    title: Text("Thông báo"),
                    content: Container(
                      child: Column(mainAxisSize: MainAxisSize.min, children: [
                        Text(
                          "Đã phát hiện tín hiệu kết nối mạng. Vui lòng đăng nhập lại hệ thống.",
                          style: TextStyle(
                              fontSize: 16, fontFamily: "PlayfairDisplay"),
                        ),
                        Container(
                          height: 50,
                          child: Center(
                            child: CircularProgressIndicator(),
                          ),
                        )
                      ]),
                    ),
                  );
                });
            Future.delayed(Duration(seconds: 3),(){
              this.currentUser=null;
              Navigator.pushNamedAndRemoveUntil(navigatorKey.currentContext!, Nav2App.loginRoute, (route) => false);
            });
            return true;
          }
          showDialog(
              context: navigatorKey.currentContext!,
              builder: (context) {
                return AlertDialog(
                  title: Text("Thông báo"),
                  content: Container(
                    child: Column(mainAxisSize: MainAxisSize.min, children: [
                      Text(
                        "Đã phát hiện tín hiệu kết nối mạng. Đang xử lý để chuyển sang chế độ online...",
                        style: TextStyle(
                            fontSize: 16, fontFamily: "PlayfairDisplay"),
                      ),
                      Container(
                        height: 50,
                        child: Center(
                          child: CircularProgressIndicator(),
                        ),
                      )
                    ]),
                  ),
                );
              });
          List<Map<String, dynamic>> queuedRequests = await getQueuedRequest();
          List<Map<String, dynamic>> remainRequest = List.from(queuedRequests);
          await removeAllQueuedRequest();
          try{
            ///NMissing how to process when request has error return from server
            for (Map<String, dynamic> request in queuedRequests) {
              Map<String, dynamic> returnStatus = await api.pushQueuedRequest(request);
              remainRequest.removeWhere((element) {
                if (element["data"] == request["data"] &&
                    element["path"] == request["path"]) {
                  return true;
                } else {
                  return false;
                }
              });
            }
          } on Exception catch(e){
            debugPrint(e.toString());
            await addAllRequestToQueue(remainRequest);
          }
          if((await getQueuedRequest()).isNotEmpty){
            showDialog(
                context: navigatorKey.currentContext!,
                builder: (context) {
                  return AlertDialog(
                    title: Text("Thông báo"),
                    content: Container(
                      child: Column(mainAxisSize: MainAxisSize.min, children: [
                        Text(
                          "Đã xảy ra lỗi trong quá trình xử lý dữ liệu. Vui lòng đăng nhập lại.",
                          style: TextStyle(
                              fontSize: 16, fontFamily: "PlayfairDisplay"),
                        ),
                        Container(
                          height: 50,
                          child: Center(
                            child: CircularProgressIndicator(),
                          ),
                        )
                      ]),
                    ),
                  );
                });
            Future.delayed(Duration(seconds: 3),(){
              this.currentUser=null;
              Navigator.pushNamedAndRemoveUntil(navigatorKey.currentContext!, Nav2App.loginRoute, (route) => false);
            });
            return true;
          }else{
            await Future.delayed(Duration(seconds: 1));
            Navigator.pushNamedAndRemoveUntil(navigatorKey.currentContext!,
                Nav2App.selectModuleroute, (route) => false);
          }
        }
        return false;
      }
    }
    return false;
  }

  prepareDataForOffline() async {
    if (!networkAvailable) {
      return;
    }
    await getAllItemForReserved().then((value) {
      this.storedItemInfo = value;
      generateSortListForItem();
    });
    await getCustomer().then((value) {
      this.storedCustomerInfo = value;
    });
    await getCategory().then((value) {
      this.storedCategoryInfo = value;
    });
    storeDataForThisSession();
  }

  generateSortListForItem() {
    List<ItemInfo> tempValue = List.from(this.storedItemInfo);
    //Sort list for created Date
    tempValue.sort((ItemInfo a, ItemInfo b) {
      return a.createdDate.compareTo(b.createdDate);
    });
    this.storedItemPageCreatedDateAsc = tempValue;
    this.storedItemPageCreatedDateDesc = tempValue.reversed.toList();
    //Sort list for item name
    tempValue = List.from(this.storedItemInfo);
    tempValue.sort((ItemInfo a, ItemInfo b) {
      return a.itemName!.toLowerCase().compareTo(b.itemName!.toLowerCase());
    });
    this.storedItemPageItemNameAsc = tempValue;
    this.storedItemPageItemNameDesc = tempValue.reversed.toList();
    //Sort list for sell price
    tempValue = List.from(this.storedItemInfo);
    tempValue.sort((ItemInfo a, ItemInfo b) {
      return a.sellPrice.compareTo(b.sellPrice);
    });
    this.storedItemPageSellPriceAsc = tempValue;
    this.storedItemPageSellPriceDesc = tempValue.reversed.toList();
  }

  ///
  /// Log in - sign up
  ///
  Future<MsgInfoCode> signUp(
      {required String name,
      required String email,
      required String password,
      required String username,
      required String phoneNumber,
      required String? gender,
      required DateTime? dateOfBirth,
      required String branchName,
      required String branchAddress}) async {
    if (name == null ||
        email == null ||
        password == null ||
        gender == null ||
        dateOfBirth == null ||
        branchName == null ||
        branchAddress == null) {
      return MsgInfoCode.inputIsNull;
    }
    Map<String, dynamic> signUpReturnMap = await api.signUp(
        name: name,
        email: email,
        password: password,
        username: username,
        gender: gender,
        dateOfBirth: DateFormat("yyyy-MM-dd").format(dateOfBirth),
        phone: phoneNumber,
        branchName: branchName,
        branchAddress: branchAddress);
    debugPrint(signUpReturnMap.toString());
    if (signUpReturnMap["state"] == "success") {
      return MsgInfoCode.signUpSuccess;
    } else {
      if (signUpReturnMap["errors"]["email"][0] ==
          "The email has already been taken.") {
        return MsgInfoCode.usernameAlreadyBeenTaken;
      }
      return MsgInfoCode.signUpFail;
    }
  }

  Future<MsgInfoCode> logInUser(String username, String password, {UserInfo? userRefresh}) async {
    // if(currentUser!=null){
    //   await logOut();
    // }
    // if (currentUser != null) {
    //   if(currentUser!.username!=username){
    //     return MsgInfoCode.alreadyHaveUserLogedIn;
    //   }
    // }
    await networkAvailableCheck();
    if (!networkAvailable) {
      await initDb(userName: username);
      UserInfo? user = await getLastSessionUser();
      if (user == null) {
        debugPrint("Can not find last session user");
        return MsgInfoCode.serverNotAvailable;
      } else {
        if (await getDataFromLastSessionForThisSession()) {
          generateSortListForItem();
          this.currentUser!.token="";
          return MsgInfoCode.logInSucess;
        } else {
          debugPrint("Failed to load data from last session");
          return MsgInfoCode.serverNotAvailable;
        }
      }
    }
    Map<String,dynamic> userInfoMap;
    UserInfo? userInfo;
    if(userRefresh==null){
      debugPrint(username);
      debugPrint(password);
      userInfoMap = await api.login(username, password);
      if (userInfoMap['statusCode'] != 200) {
        return MsgInfoCode.serverNotAvailable;
      }
      if (userInfoMap["state"] != null) {
        if (userInfoMap["state"] == "fail") {
          if (userInfoMap["errors"] ==
              "Invalid username, password or account is disable") {
            return MsgInfoCode.wrongPasswordOrUsername;
          }
        }
      }
      String? token = userInfoMap["token"];
      userInfoMap = userInfoMap["user_info"][0];
      var response = await Dio().get(ServerConfig.projectUrl+userInfoMap["avatar_url"].toString(),options: Options(responseType: ResponseType.bytes)); // <--2
      var documentDirectory = await getApplicationDocumentsDirectory();
      var firstPath = documentDirectory.path + "/avatar";
      String avatarName = userInfoMap["avatar_url"].toString().split("/").last;
      var filePathAndName = documentDirectory.path + '/'+avatarName;
      await Directory(firstPath).create(recursive: true); // <-- 1
      File file2 = new File(filePathAndName);             // <-- 2
      file2.writeAsBytesSync(response.data);
      userInfo = UserInfo(
          token: token,
          userId: userInfoMap["user_id"].toString(),
          name: userInfoMap["name"].toString(),
          username: userInfoMap["username"].toString(),
          email: userInfoMap["email"].toString(),
          phone: userInfoMap["phone"].toString(),
          gender: userInfoMap["gender"].toString(),
          dateOfBirth: userInfoMap["date_of_birth"].toString(),
          storeId: userInfoMap["store_id"].toString(),
          storeName: userInfoMap["storeName"].toString(),
          storeOwnerId: userInfoMap["store_owner_id"].toString(),
          branchId: userInfoMap["branch_id"].toString(),
          branchName: userInfoMap["branch_name"].toString(),
          branchAddress: userInfoMap["branches_address"].toString(),
          roles: userInfoMap["roles"],
          avatarUrl: userInfoMap["avatar_url"].toString(),
          avatarFile: file2.path);
    }else{
      this.currentUser=userRefresh;
      userInfo= await getUserInfo(userRefresh);
      if(userInfo==null){
        this.currentUser=null;
        return MsgInfoCode.serverNotAvailable;
      }
    }
    this.currentUser = userInfo;
    await initDb(userName: this.currentUser!.username);
    await storeUserLoggedIn(this.currentUser!);
    await initDb();
    List<Map<String, dynamic>> listQueuedRequest = await getQueuedRequest();
    if (listQueuedRequest.isNotEmpty) {
      showDialog(
          context: navigatorKey.currentContext!,
          builder: (context) {
            return AlertDialog(
              title: Text("Thông báo"),
              content: Container(
                child: Column(mainAxisSize: MainAxisSize.min, children: [
                  Text(
                    "Đang xử lý các dữ liệu từ lần sử dụng chế độ offline trước...",
                    style: TextStyle(fontSize: 16),
                  ),
                  Container(
                    height: 50,
                    child: Center(
                      child: CircularProgressIndicator(),
                    ),
                  )
                ]),
              ),
            );
          });
      if (!(await networkAvailableCheck())) {
        showDialog(
            context: navigatorKey.currentContext!,
            builder: (context) {
              return WillPopScope(
                onWillPop: () async {
                  Navigator.pop(context);
                  return false;
                },
                child: AlertDialog(
                  title: Text("Thông báo"),
                  content: Container(
                    child: Column(mainAxisSize: MainAxisSize.min, children: [
                      Text(
                        "Không xử lý được do không có mạng!!",
                        style: TextStyle(fontSize: 16),
                      ),
                    ]),
                  ),
                  actions: [
                    TextButton(
                        onPressed: () {
                          Navigator.pop(context);
                        },
                        child: Text("Đóng"))
                  ],
                ),
              );
            });
        Future.delayed(Duration(seconds: 2));
        return MsgInfoCode.serverNotAvailable;
      } else {
        List<Map<String, dynamic>> queuedRequests = await getQueuedRequest();
        List<Map<String, dynamic>> remainRequest = List.from(queuedRequests);
        await removeAllQueuedRequest();
        for (Map<String, dynamic> request in queuedRequests) {
          Map<String, dynamic> returnStatus =
              await api.pushQueuedRequest(request);
          if (returnStatus["state"] != "success") {
            await addAllRequestToQueue(remainRequest);
            break;
          }
          remainRequest.removeWhere((element) {
            if (element["data"] == request["data"] &&
                element["path"] == request["path"]) {
              return true;
            } else {
              return false;
            }
          });
        }
        await Future.delayed(Duration(seconds: 1));
        Navigator.pop(navigatorKey.currentContext!);
      }
    }
    prepareDataForOffline();
    if(this.currentUser!.roles.contains("purchasing")){
      getItemsWithNoPurchasePrice().then((value)async{
        if(value.isNotEmpty){
          const AndroidNotificationDetails androidPlatformChannelSpecifics =
          AndroidNotificationDetails(
              '0', 'no_purchase_price', 'Notification chanel for items with no purchase price',
              importance: Importance.max,
              priority: Priority.high,
              playSound: true,
              showWhen: false);
          const NotificationDetails platformChannelSpecifics =
          NotificationDetails(android: androidPlatformChannelSpecifics);
          await flutterLocalNotificationsPlugin.show(
              0, 'Thông báo BKRM', 'Có sản phẩm đang không có giá nhập', platformChannelSpecifics,
              payload: 'have_items_with_no_purchase_price');
        }
      });
    }
    this.timer = Timer.periodic(Duration(minutes: 10), (timer) {
      if(this.currentUser==null){
        timer.cancel();
      }
      if(this.currentUser!.roles.contains("purchasing")){
        getItemsWithNoPurchasePrice().then((value)async{
          if(value.isNotEmpty){
            const AndroidNotificationDetails androidPlatformChannelSpecifics =
            AndroidNotificationDetails(
                '0', 'no_purchase_price', 'Notification chanel for items with no purchase price',
                importance: Importance.max,
                priority: Priority.high,
                showWhen: false);
            const NotificationDetails platformChannelSpecifics =
            NotificationDetails(android: androidPlatformChannelSpecifics);
            await flutterLocalNotificationsPlugin.show(
                0, 'Thông báo BKRM', 'Có sản phẩm đang không có giá nhập', platformChannelSpecifics,
                payload: 'have_items_with_no_purchase_price');
          }
        });
      }
      prepareDataForOffline();
    });
    return MsgInfoCode.logInSucess;
  }

  Future<void> logOut() async {
    await networkAvailableCheck();
    if (networkAvailable&&BkrmService().currentUser!=null) {
      try{
        await api.logOut();
      }on Exception catch(e){
        debugPrint(e.toString());
      }
    }
    this.currentUser!.stayLoggedIn=false;
    await storeUserLoggedIn(this.currentUser!);
    timer?.cancel();
    _cart = null;
    _importGood = null;
    listCart.clear();
    _noSqlDb = null;
    currentUser = null;
    networkAvailable = true;
  }

  Future<MsgInfoCode> changePassword(
      String oldPassword, String newPassword) async {
    Map<String, dynamic> returnResult = (await api.changePassword(
        this.currentUser!.storeId!,
        this.currentUser!.branchId!,
        {"old_password": oldPassword, "new_password": newPassword}))!;
    if (returnResult["state"] == "success") {
      return MsgInfoCode.actionSuccess;
    } else {
      if (returnResult["errors"] == "Wrong password") {
        return MsgInfoCode.wrongPasswordOrUsername;
      }
      debugPrint(returnResult["errors"].toString());
      return MsgInfoCode.actionFail;
    }
  }

  ///
  /// Create method
  ///
  ///
  ///
  ///
  ///
  ///
  ///
  Future<MsgInfoCode?> createEmployeeUser(
      {String? name,
      required String username,
      required String password,
      String? email,
      String? phoneNumber,
      String? gender,
      DateTime? dateOfBirth,
      required bool? selling,
      required bool? editing,
      required bool? purchasing,
      required bool? reporting}) async {
    if (currentUser == null) {
      return null;
    }
    await networkAvailableCheck();
    if (!networkAvailable) {
      return MsgInfoCode.serverNotAvailable;
    }
    debugPrint("storeOwnerId");
    debugPrint(currentUser!.storeOwnerId.toString());
    debugPrint("userId");
    debugPrint(currentUser!.userId.toString());
    if (await checkToChangeToOfflineMode()) {
      return null;
    }
    if (currentUser!.storeOwnerId != currentUser!.userId) {
      return null;
    }
    String? dateOfBirthString;
    if (dateOfBirth == null) {
      dateOfBirthString = null;
    } else {
      dateOfBirthString = DateFormat("yyyy-MM-dd").format(dateOfBirth);
    }
    Map<String, dynamic> returnStatus = await api.createEmployeeUser({
      (name == null ? "not_have_name" : "name"): name,
      "username": username,
      "password": password,
      (email == null ? "not_have_email" : "email"): email,
      (phoneNumber == null ? "not_have_phone" : "phone"): phoneNumber,
      (gender != null ? "gender" : "not_have_gender"): gender,
      (dateOfBirthString == null ? "not_have_date_of_birth" : "date_of_birth"):
          dateOfBirthString,
      "selling": selling == true ? 1 : 0,
      "managing": editing == true ? 1 : 0,
      "purchasing": purchasing == true ? 1 : 0,
      "reporting": reporting == true ? 1 : 0
    }, currentUser!.storeId, currentUser!.branchId);
    debugPrint(returnStatus.toString());
    if (returnStatus["state"] == "success") {
      return MsgInfoCode.signUpSuccess;
    } else {
      debugPrint(returnStatus["errors"].toString());
      if (returnStatus["errors"]["username"][0] ==
          "The username has already been taken.") {
        return MsgInfoCode.usernameAlreadyBeenTaken;
      }
      return MsgInfoCode.signUpFail;
    }
  }

  Future<MsgInfoCode?> createCustomer({
    required String phoneNumber,
    required String name,
    String? address,
    String? gender,
    DateTime? dateOfBirth,
    String? email,
  }) async {
    if (await checkToChangeToOfflineMode()) {
      return null;
    }
    if (currentUser == null) {
      return null;
    }
    if (!currentUser!.roles.contains("selling") &&
        !currentUser!.roles.contains("managing")) {
      return MsgInfoCode.actionFail;
    }
    String? formattedDate = dateOfBirth != null
        ? DateFormat("yyyy-MM-dd").format(dateOfBirth)
        : null;
    Map<String,dynamic> customerMap = {
      "phone": phoneNumber,
      "name": name,
    };
    if(address!=null){
      customerMap["address"]=address;
    }
    if(email!=null){
      customerMap["email"]=email;
    }
    if(gender!=null){
      customerMap["gender"]=gender;
    }
    if(formattedDate!=null){
      customerMap["date_of_birth"]=formattedDate;
    }
    Map<String, dynamic> returnStatus = await api.createCustomer(customerMap, currentUser!.storeId, currentUser!.branchId);
    if (!networkAvailable) {
      for (CustomerInfo customer in storedCustomerInfo) {
        if (customer.phoneNumber == phoneNumber) {
          return MsgInfoCode.phoneNumberAlreadyBeenTaken;
        }
      }
      CustomerInfo newCustomer = CustomerInfo(
          id: (-1).toString(),
          storeId: currentUser!.storeId.toString(),
          name: name,
          phoneNumber: phoneNumber,
          customerPoint: 0.toString(),
          email: email ?? "null",
          address: address ?? "null",
          gender: gender ?? "null",
          dateOfBirth: dateOfBirth == null
              ? "null"
              : DateFormat("yyyy-MM-dd HH:mm:ss").format(dateOfBirth),
          customerCode: "null",
          createdDate: DateFormat("yyyy-MM-dd HH:mm:ss").format(DateTime.now()),
          deleted: 0.toString());
      this.storedCustomerInfo.add(newCustomer);
      return MsgInfoCode.signUpSuccess;
    }
    debugPrint(returnStatus.toString());
    if (returnStatus["state"] == "success") {
      return MsgInfoCode.signUpSuccess;
    } else {
      if (returnStatus["errors"]["phone"] != null) {
        return MsgInfoCode.phoneNumberAlreadyBeenTaken;
      }
      return MsgInfoCode.signUpFail;
    }
  }

  Future<MsgInfoCode?> createSupplier(
      {required String phoneNumber,
      String? name,
      String? email,
      String? address}) async {
    if (await checkToChangeToOfflineMode()) {
      return null;
    }
    ;
    if (this.currentUser == null) {
      return null;
    }
    if (!this.currentUser!.roles.contains("purchasing")) {
      return null;
    }
    Map<String, dynamic> returnStatus = await api.createSupplier({
      "phone": phoneNumber,
      (email != null ? "email" : "not_have_email"): email,
      "name": name,
      (address != null ? "address" : "not_have_address"): address,
    }, currentUser!.storeId, currentUser!.branchId);
    debugPrint(returnStatus.toString());
    if (returnStatus["state"] == "success") {
      return MsgInfoCode.signUpSuccess;
    } else {
      if (returnStatus["errors"]["phone_number"][0] ==
          "The phone number has already been taken.") {
        return MsgInfoCode.phoneNumberAlreadyBeenTaken;
      }
      return MsgInfoCode.signUpFail;
    }
  }

  Future<MsgInfoCode?> createCategory({
    required String name,
  }) async {
    if (await checkToChangeToOfflineMode()) {
      return null;
    }
    ;
    if (this.currentUser == null) {
      return null;
    }
    if (!this.currentUser!.roles.contains("purchasing")) {
      return null;
    }
    Map<String, dynamic> returnStatus = await api.createCategory(
        {"name": name}, currentUser!.storeId, currentUser!.branchId);
    debugPrint(returnStatus.toString());
    if (returnStatus["state"] == "success") {
      return MsgInfoCode.actionSuccess;
    } else {
      for (String error in returnStatus["errors"]) {
        debugPrint(error);
      }
      return MsgInfoCode.actionFail;
    }
  }

  Future<MsgInfoCode?> createNewProduct(
      {required int categoryId,
      required String itemName,
      required String? barCode,
      required int quantity,
      required int sellValue,
        required int purchasePrice,
      required File? imageFile,
      Function(ItemInfo?)? processReturnProduct}) async {
    if (await checkToChangeToOfflineMode()) {
      return null;
    }
    if (currentUser == null) {
      return null;
    }
    if (!currentUser!.roles.contains("purchasing")) {
      return null;
    }
    Map<String, dynamic> productMap = {
      "category_id": categoryId,
      "item_name": itemName,
      "bar_code": barCode,
      "quantity": quantity,
      "purchase_price":purchasePrice,
      "sell_price": sellValue,
    };
    debugPrint(productMap.toString());
    productMap["image"] = imageFile != null
        ? await MultipartFile.fromFile(imageFile.path,
            filename: imageFile.path.split('/').last)
        : null;
    var response = await api.createNewProduct(
        productMap, currentUser!.storeId, currentUser!.branchId);
    if (response["state"] == "success") {
      if(processReturnProduct!=null){
        ItemInfo item = ItemInfo(
          itemId: response["item"]["item_id"].toString(),
          itemName: response["item"]["item_name"].toString(),
          barCode: response["item"]["bar_code"].toString(),
          imageUrl: response["item"]["image_url"].toString(),
          createdDate: response["item"]["created_datetime"].toString(),
          categoryId: response["item"]["category_id"].toString(),
          categoryName: response["item"]["category_name"].toString(),
          quantity: response["item"]["quantity"].toString(),
          purchasePrice: response["item"]["purchase_price"],
          priceId: response["item"]["price_id"].toString(),
          sellPrice: response["item"]["sell_price"].toString(),
          pointRatio: response["item"]["point_ratio"].toString()
        );
        processReturnProduct(item);
      }
      return MsgInfoCode.actionSuccess;
    } else {
      debugPrint(response["errors"].toString());
      return MsgInfoCode.actionFail;
    }
  }

  Future<MsgInfoCode?> createRefundSheet(DetailInvoiceInfo? invoice,
      String reason, List<Map<String, dynamic>> refundItemsMap) async {
    if (await checkToChangeToOfflineMode()) {
      return null;
    }
    ;
    if (this.currentUser == null) {
      return null;
    }
    if (!this.currentUser!.roles.contains("selling")) {
      return null;
    }

    Map<String, dynamic> returnStatus = await api.createRefundSheet({
      "refund_sheet": {
        "invoice_id": invoice!.invoiceInfo.invoiceId,
        "reason": reason
      },
      "refund_items": refundItemsMap
    }, currentUser!.storeId, currentUser!.branchId);
    debugPrint(returnStatus.toString());
    if (returnStatus["state"] == "success") {
      return MsgInfoCode.actionSuccess;
    } else {
      for (String error in returnStatus["errors"]) {
        debugPrint(error);
      }
      return MsgInfoCode.actionFail;
    }
  }


  Future<MsgInfoCode?> createReturnPurchasedSheet(DetailPurchasedSheetInfo? purchasedSheet,
      String reason, List<Map<String, dynamic>> refundItemsMap) async {
    if (await checkToChangeToOfflineMode()) {
      return null;
    }
    ;
    if (this.currentUser == null) {
      return null;
    }
    if (!this.currentUser!.roles.contains("selling")) {
      return null;
    }

    Map<String, dynamic> returnStatus = await api.createRefundPurchaseSheet({
      "return_purchased_sheet": {
        "purchased_sheet_id": purchasedSheet!.importInvoiceInfo.purchasedSheetId,
      },
      "return_purchased_items": refundItemsMap
    }, currentUser!.storeId, currentUser!.branchId);
    debugPrint(returnStatus.toString());
    if (returnStatus["state"] == "success") {
      return MsgInfoCode.actionSuccess;
    } else {
      debugPrint(returnStatus["error"].toString());
      return MsgInfoCode.actionFail;
    }
  }

  Future<MsgInfoCode?> createShift(
      {required String name,
      required DateTime? startTime,
      required DateTime? endTime,
      required bool monday,
      required bool tuesday,
      required bool wednesday,
      required bool thursday,
      required bool friday,
      required bool saturday,
      required bool sunday,
      required DateTime? startDate,
      required DateTime? endDate}) async {
    if (await checkToChangeToOfflineMode()) {
      return null;
    }
    ;
    if (this.currentUser == null) {
      return null;
    }
    if (this.currentUser!.userId != this.currentUser!.storeOwnerId) {
      return null;
    }
    Map<String, dynamic> returnStatus = await api.createShift({
      "name": name != null ? name : "",
      "start_time": startTime != null
          ? DateFormat("HH:mm:ss").format(startTime)
          : "00:00:00",
      "end_time":
          endTime != null ? DateFormat("HH:mm:ss").format(endTime) : "00:00:00",
      "monday": monday,
      "tuesday": tuesday,
      "wednesday": wednesday,
      "thursday": thursday,
      "friday": friday,
      "saturday": saturday,
      "sunday": sunday,
      "start_date": startDate != null
          ? DateFormat("yyyy-MM-dd").format(startDate)
          : DateFormat("yyyy-MM-dd")
              .format(DateTime.fromMicrosecondsSinceEpoch(0)),
      "end_date":
          endDate != null ? DateFormat("yyyy-MM-dd").format(endDate) : null,
    }, currentUser!.storeId, currentUser!.branchId);
    debugPrint(returnStatus.toString());
    if (returnStatus["state"] == "success") {
      return MsgInfoCode.actionSuccess;
    } else {
      for (String error in returnStatus["errors"]) {
        debugPrint(error);
      }
      return MsgInfoCode.actionFail;
    }
  }

  Future<MsgInfoCode?> createSchedule(
      {required int? shiftId,
      required List<int?> userListId,
      required DateTime? startDate,
      required DateTime? endDate}) async {
    if (await checkToChangeToOfflineMode()) {
      return null;
    }
    ;
    if (this.currentUser == null) {
      return null;
    }
    if (!this.currentUser!.roles.contains("managing")) {
      return null;
    }

    Map<String, dynamic> returnStatus = await api.createSchedule({
      "shift_id": shiftId,
      "user_id_list": userListId,
      "start_date": startDate != null
          ? DateFormat("yyyy-MM-dd").format(startDate)
          : DateFormat("yyyy-MM-dd")
              .format(DateTime.fromMicrosecondsSinceEpoch(0)),
      "end_date":
          endDate != null ? DateFormat("yyyy-MM-dd").format(endDate) : null,
    }, currentUser!.storeId, currentUser!.branchId);
    debugPrint(returnStatus.toString());
    if (returnStatus["state"] == "success") {
      return MsgInfoCode.actionSuccess;
    } else {
      if (returnStatus["errors"].toString().contains("is already working in")) {
        return MsgInfoCode.alreadyWorkInShift;
      }
      for (String error in returnStatus["errors"]) {
        debugPrint(error);
      }
      return MsgInfoCode.actionFail;
    }
  }

  Future<MsgInfoCode?> createAttendance(List<ScheduleInfo> scheduleList) async {
    if (await checkToChangeToOfflineMode()) {
      return null;
    }
    ;
    if (this.currentUser == null) {
      return null;
    }
    if (!this.currentUser!.roles.contains("managing")) {
      return null;
    }
    debugPrint(scheduleList.first.scheduleId.toString());
    Map<String, dynamic> returnStatus = await api.createAttendance(
        {"schedule_id_list": scheduleList.map((e) => e.scheduleId!).toList()},
        currentUser!.storeId,
        currentUser!.branchId);
    debugPrint(returnStatus.toString());
    if (returnStatus["state"] == "success") {
      return MsgInfoCode.actionSuccess;
    } else {
      debugPrint(returnStatus["errors"].toString());
      return MsgInfoCode.actionFail;
    }
  }

  Future<MsgInfoCode?> createQuantityCheckingSheet(String reason, List<Map<String,dynamic>> itemList) async {
    if (await checkToChangeToOfflineMode()) {
      return null;
    }
    ;
    if (this.currentUser == null) {
      return null;
    }
    if (!this.currentUser!.roles.contains("purchasing")) {
      return null;
    }
    Map<String, dynamic> returnStatus = await api.createQuantityCheckingSheet(
        {"reason":reason,
        "item_list":itemList},
        currentUser!.storeId,
        currentUser!.branchId);
    debugPrint(returnStatus.toString());
    if (returnStatus["state"] == "success") {
      return MsgInfoCode.actionSuccess;
    } else {
      debugPrint(returnStatus["errors"].toString());
      return MsgInfoCode.actionFail;
    }
  }
  ///
  /// Get method
  ///
  ///
  ///
  Future<List<ItemInfo>> getAllItemForReserved() async {
    if (await checkToChangeToOfflineMode()) {
      return [];
    }
    if (currentUser == null) {
      return [];
    }
    if (!currentUser!.roles.contains("selling") &&
        !currentUser!.roles.contains("purchasing")) {
      return [];
    }
    Map<String, dynamic> itemMap = await api.getItemInfoNoPagination(
        this.currentUser!.storeId, this.currentUser!.branchId);
    List<ItemInfo> items = <ItemInfo>[];
    for (Map<String, dynamic> item in itemMap["item"]) {
      print("Begin parse item:");
      ItemInfo tempItem = ItemInfo(
          itemId: item["item_id"].toString(),
          itemName: item["item_name"].toString(),
          categoryId: item["category_id"].toString(),
          categoryName: item["category_name"].toString(),
          barCode: item["bar_code"].toString(),
          imageUrl: item["image_url"].toString(),
          sellPrice: item["sell_price"].toString(),
          priceId: item["price_id"].toString(),
          quantity: item["quantity"].toString(),
          purchasePrice: item["purchase_price"].toString(),
          createdDate: item["created_datetime"].toString(),
      pointRatio: item["point_ratio"].toString());
      items.add(tempItem);
    }
    return items;
  }

  List<ItemInfo> filterStoredList(List<ItemInfo> listItem,
      {String? barcode, String? searchQuery, int? categoryId}) {
    List<ItemInfo> listReceivedItem = List.from(listItem);
    if (searchQuery != null) {
      listReceivedItem.removeWhere((element) =>
          !element.itemName!.toLowerCase().contains(searchQuery.toLowerCase()));
    }
    if (barcode != null) {
      listReceivedItem.removeWhere((element) =>
          !element.itemName!.toLowerCase().contains(barcode.toLowerCase()));
    }
    if (categoryId != null) {
      listReceivedItem
          .removeWhere((element) => element.categoryId != categoryId);
    }
    return listReceivedItem;
  }

  ItemPage? getStoredListItem(
      {required int page,
      required String orderBy,
      required String order,
      String? searchQuery,
      String? barcode,
      int? categoryId}) {
    switch (orderBy) {
      case "created_date":
        if (order == "asc") {
          if (searchQuery == null && categoryId == null && barcode == null) {
            ItemPage itemPage = ItemPage(
                page,
                storedItemPageCreatedDateAsc.length ~/ 10,
                10,
                this.storedItemPageCreatedDateAsc.sublist(
                    (page - 1) * 10,
                    page * 10 < this.storedItemPageCreatedDateAsc.length - 1
                        ? page * 10
                        : this.storedItemPageCreatedDateAsc.length - 1));
            return itemPage;
          } else {
            List<ItemInfo> listReceivedItem = filterStoredList(
                storedItemPageCreatedDateAsc,
                barcode: barcode,
                searchQuery: searchQuery,
                categoryId: categoryId);
            ItemPage itemPage =
                ItemPage(page, page, listReceivedItem.length, listReceivedItem);
            return itemPage;
          }
        } else {
          if (searchQuery == null && categoryId == null && barcode == null) {
            ItemPage itemPage = ItemPage(
                page,
                storedItemPageCreatedDateDesc.length ~/ 10,
                10,
                this.storedItemPageCreatedDateDesc.sublist(
                    (page - 1) * 10,
                    page * 10 < this.storedItemPageCreatedDateDesc.length - 1
                        ? page * 10
                        : this.storedItemPageCreatedDateDesc.length - 1));
            return itemPage;
          } else {
            List<ItemInfo> listReceivedItem = filterStoredList(
                storedItemPageCreatedDateDesc,
                barcode: barcode,
                searchQuery: searchQuery,
                categoryId: categoryId);
            ItemPage itemPage =
                ItemPage(page, page, listReceivedItem.length, listReceivedItem);
            return itemPage;
          }
        }
      case "sell_price":
        if (order == "asc") {
          if (searchQuery == null && categoryId == null && barcode == null) {
            ItemPage itemPage = ItemPage(
                page,
                storedItemPageSellPriceAsc.length ~/ 10,
                10,
                this.storedItemPageSellPriceAsc.sublist(
                    (page - 1) * 10,
                    page * 10 < this.storedItemPageSellPriceAsc.length - 1
                        ? page * 10
                        : this.storedItemPageSellPriceAsc.length - 1));
            return itemPage;
          } else {
            List<ItemInfo> listReceivedItem = filterStoredList(
                storedItemPageSellPriceAsc,
                barcode: barcode,
                searchQuery: searchQuery,
                categoryId: categoryId);
            ItemPage itemPage =
                ItemPage(page, page, listReceivedItem.length, listReceivedItem);
            return itemPage;
          }
        } else {
          if (searchQuery == null && categoryId == null && barcode == null) {
            ItemPage itemPage = ItemPage(
                page,
                storedItemPageSellPriceDesc.length ~/ 10,
                10,
                this.storedItemPageSellPriceDesc.sublist(
                    (page - 1) * 10,
                    page * 10 < this.storedItemPageSellPriceDesc.length - 1
                        ? page * 10
                        : this.storedItemPageSellPriceDesc.length - 1));
            return itemPage;
          } else {
            List<ItemInfo> listReceivedItem = filterStoredList(
                storedItemPageSellPriceDesc,
                barcode: barcode,
                searchQuery: searchQuery,
                categoryId: categoryId);
            ItemPage itemPage =
                ItemPage(page, page, listReceivedItem.length, listReceivedItem);
            return itemPage;
          }
        }
      case "item_name":
        if (order == "asc") {
          if (searchQuery == null && categoryId == null && barcode == null) {
            ItemPage itemPage = ItemPage(
                page,
                storedItemPageSellPriceAsc.length ~/ 10,
                10,
                this.storedItemPageSellPriceAsc.sublist(
                    (page - 1) * 10,
                    page * 10 < this.storedItemPageSellPriceAsc.length - 1
                        ? page * 10
                        : this.storedItemPageSellPriceAsc.length - 1));
            return itemPage;
          } else {
            List<ItemInfo> listReceivedItem = filterStoredList(
                storedItemPageSellPriceAsc,
                barcode: barcode,
                searchQuery: searchQuery,
                categoryId: categoryId);
            ItemPage itemPage =
                ItemPage(page, page, listReceivedItem.length, listReceivedItem);
            return itemPage;
          }
        } else {
          if (searchQuery == null && categoryId == null && barcode == null) {
            ItemPage itemPage = ItemPage(
                page,
                storedItemPageItemNameDesc.length ~/ 10,
                10,
                this.storedItemPageItemNameDesc.sublist(
                    (page - 1) * 10,
                    page * 10 < this.storedItemPageItemNameDesc.length - 1
                        ? page * 10
                        : this.storedItemPageItemNameDesc.length - 1));
            return itemPage;
          } else {
            List<ItemInfo> listReceivedItem = filterStoredList(
                storedItemPageItemNameDesc,
                barcode: barcode,
                searchQuery: searchQuery,
                categoryId: categoryId);
            ItemPage itemPage =
                ItemPage(page, page, listReceivedItem.length, listReceivedItem);
            return itemPage;
          }
        }
      default:
        return null;
    }
  }

  Future<ItemPage?> getAllItem(
      {required int page,
      required String orderBy,
      required String order,
      String? searchQuery,
      String? barcode,
      int? categoryId}) async {
    if (await checkToChangeToOfflineMode()) {
      return null;
    }
    if (currentUser == null) {
      return null;
    }
    if (!currentUser!.roles.contains("selling") &&
        !currentUser!.roles.contains("purchasing")) {
      return null;
    }
    if (!networkAvailable) {
      return getStoredListItem(
          page: page,
          orderBy: orderBy,
          order: order,
          searchQuery: searchQuery,
          barcode: barcode,
          categoryId: categoryId);
    }
    Map<String, dynamic> filterMap = {"order_by": orderBy, "order": order};
    if (searchQuery != null) {
      filterMap["search_query"] = searchQuery;
    }
    if (barcode != null) {
      filterMap["bar_code"] = barcode;
    }
    if (categoryId != null) {
      filterMap["category_id"] = categoryId;
    }
    Map<String, dynamic> itemMap = await api.getItemInfoPagination(
        currentUser!.storeId, currentUser!.branchId,
        page: page, filterMap: filterMap);
    debugPrint(itemMap.toString());
    List<ItemInfo> items = <ItemInfo>[];
    for (Map<String, dynamic> item in itemMap["item"]["data"]) {
      print("Begin parse item:");
      ItemInfo tempItem = ItemInfo(
          itemId: item["item_id"].toString(),
          itemName: item["item_name"].toString(),
          categoryId: item["category_id"].toString(),
          categoryName: item["category_name"].toString(),
          barCode: item["bar_code"].toString(),
          imageUrl: item["image_url"].toString(),
          sellPrice: item["sell_price"].toString(),
          priceId: item["price_id"].toString(),
          quantity: item["quantity"].toString(),
          purchasePrice: item["purchase_price"].toString(),
          createdDate: item["created_datetime"].toString(),
      pointRatio: item["point_ratio"].toString());
      items.add(tempItem);
    }
    ItemPage itemPage = ItemPage(
        int.tryParse(itemMap["item"]["current_page"].toString()) != null
            ? int.parse(itemMap["item"]["current_page"].toString())
            : -1,
        int.tryParse(itemMap["item"]["last_page"].toString()) != null
            ? int.parse(itemMap["item"]["last_page"].toString())
            : -1,
        int.tryParse(itemMap["item"]["per_page"].toString()) != null
            ? int.parse(itemMap["item"]["per_page"].toString())
            : -1,
        items);
    return itemPage;
  }

  Future<List<ItemInfo>> getItems({
    int page = 1,
    required List<int> itemId,
  }) async {
    if (await checkToChangeToOfflineMode()) {
      return [];
    }
    if (!currentUser!.roles.contains("selling") &&
        !currentUser!.roles.contains("purchasing")) {
      return [];
    }
    if (!networkAvailable) {
      List<ItemInfo> listItems = [];
      for (ItemInfo item in this.storedItemInfo) {
        if (itemId.contains(item.itemId)) {
          listItems.add(item);
          itemId.removeWhere((element) => element == item.itemId);
        }
      }
      return listItems;
    }
    Map<String, dynamic> returnItem = await api.getItemInfoPagination(
        currentUser!.storeId, currentUser!.branchId,
        page: page, filterMap: {"item_id": itemId});
    List<ItemInfo> listItems = [];
    debugPrint(returnItem.toString());
    if (returnItem["state"] == "success") {
      for (Map<String, dynamic> item in returnItem["item"]) {
        ItemInfo tempItem = ItemInfo(
            itemName: item["item_name"],
            itemId: item["item_id"].toString(),
            categoryId: item["category_id"].toString(),
            categoryName: item["category_name"].toString(),
            barCode: item["bar_code"].toString(),
            imageUrl: item["image_url"].toString(),
            sellPrice: item["sell_price"].toString(),
            priceId: item["price_id"].toString(),
            quantity: item["quantity"].toString(),
            purchasePrice: item["purchase_price"].toString(),
            createdDate: item["created_datetime"].toString(),
        pointRatio: item["pointRatio"].toString());
        listItems.add(tempItem);
      }
      return listItems;
    } else {
      debugPrint(returnItem.toString());
      debugPrint(returnItem["errors"].toString());
      return [];
    }
  }

  Future<List<ItemInfo>> searchItemInBranch(
      {String? barCode, List<int?>? priceId}) async {
    if (await checkToChangeToOfflineMode()) {
      return [];
    }
    if (barCode == null && priceId == null) {
      return [];
    }
    if (!currentUser!.roles.contains("selling") &&
        !currentUser!.roles.contains("purchasing")) {
      return [];
    }
    Map<String, dynamic> returnItem = await api.searchItem({
      (barCode == null ? "not_have_barcode" : "bar_code"): barCode,
      ((priceId == null || priceId.isEmpty) ? "not_have_price_id" : "price_id"):
          priceId
    }, currentUser!.storeId, currentUser!.branchId);
    debugPrint(returnItem.toString());
    if (returnItem["state"] == "success") {
      List<ItemInfo> returnItems = [];
      for (Map<String, dynamic> item in returnItem["item"]) {
        ItemInfo tempItem = ItemInfo(
            itemName: item["item_name"],
            itemId: item["item_id"].toString(),
            categoryId: item["category_id"].toString(),
            categoryName: item["category_name"].toString(),
            barCode: item["bar_code"].toString(),
            imageUrl: item["image_url"].toString(),
            sellPrice: item["sell_price"].toString(),
            priceId: item["price_id"].toString(),
            quantity: item["quantity"].toString(),
            purchasePrice: item["purchase_price"].toString(),
            createdDate: item["created_datetime"].toString(),
            pointRatio: item["point_ratio"].toString());
        returnItems.add(tempItem);
        debugPrint(tempItem.toString());
      }
      return returnItems;
    } else {
      debugPrint(returnItem.toString());
      debugPrint(returnItem["errors"].toString());
      return [];
    }
  }

  Future<List<DefaultItemInfo>> searchItemInDefaultDb(
      {String? barCode, List<int>? priceId}) async {
    if (await checkToChangeToOfflineMode()) {
      return [];
    }
    if (barCode == null && priceId == null) {
      return [];
    }
    if (!currentUser!.roles.contains("selling") &&
        !currentUser!.roles.contains("purchasing")) {
      return [];
    }
    Map<String, dynamic> returnItem = await api.searchDefaultItem({
      (barCode == null ? "not_have_barcode" : "bar_code"): barCode,
      ((priceId == null || priceId.isEmpty) ? "not_have_price_id" : "price_id"):
          priceId
    }, currentUser!.storeId, currentUser!.branchId);
    debugPrint(returnItem.toString());
    if (returnItem["state"] == "success") {
      List<DefaultItemInfo> returnItems = [];
      for (Map<String, dynamic> item in returnItem["item"]) {
        DefaultItemInfo tempItem = DefaultItemInfo(
            id: item["id"].toString(),
            categoryId: item["category_id"].toString(),
            categoryName: item["category_name"].toString(),
            name: item["name"].toString(),
            barCode: item["bar_code"].toString(),
            imageUrl: item["image_url"].toString(),
            createdDate: item["created_date"].toString(),
            deleted: item["deleted"].toString());
        returnItems.add(tempItem);
      }
      return returnItems;
    } else {
      debugPrint(returnItem.toString());
      debugPrint(returnItem["errors"].toString());
      return [];
    }
  }

  Future<DashboardInfo?> getDashboardInfo() async {
    if (await checkToChangeToOfflineMode()) {
      return null;
    }
    ;
    if (currentUser == null) {
      return null;
    }
    Map<String, dynamic> dashboardInfo =
        await api.getDashboardInfo(currentUser!.storeId, currentUser!.branchId);
    List<DayRevenue> weekRevenue = <DayRevenue>[];
    for (int i = 0; i < 7; i++) {
      Map<String, dynamic> dayRevenueMap = dashboardInfo["revenue_month"][i];
      DayRevenue dayRevenue = DayRevenue(
          DateTime.tryParse(dayRevenueMap["date"]) == null
              ? DateTime.fromMicrosecondsSinceEpoch(0)
              : DateTime.tryParse(dayRevenueMap["date"].toString()),
          int.tryParse(dayRevenueMap["revenue"]["total_sell_price"]
                      .toString()) ==
                  null
              ? 0
              : int.tryParse(
                  dayRevenueMap["revenue"]["total_sell_price"].toString()));
      weekRevenue.add(dayRevenue);
    }

    List<DayRevenue> monthRevenue = <DayRevenue>[];
    for (Map<String, dynamic> dayRevenueMap in dashboardInfo["revenue_month"]) {
      DayRevenue dayRevenue = DayRevenue(
          DateTime.tryParse(dayRevenueMap["date"].toString()) == null
              ? DateTime.fromMicrosecondsSinceEpoch(0)
              : DateTime.tryParse(dayRevenueMap["date"].toString()),
          int.tryParse(dayRevenueMap["revenue"]["total_sell_price"]
                      .toString()) ==
                  null
              ? 0
              : int.tryParse(
                  dayRevenueMap["revenue"]["total_sell_price"].toString()));
      monthRevenue.add(dayRevenue);
    }
    List<MonthRevenue> yearRevenue = <MonthRevenue>[];
    for (Map<String, dynamic> monthRevenueMap
        in dashboardInfo["revenue_year"]) {
      MonthRevenue monthRevenue = MonthRevenue(
          DateTime.tryParse(monthRevenueMap["year-month"].toString()) == null
              ? DateTime.fromMicrosecondsSinceEpoch(0)
              : DateTime.tryParse(monthRevenueMap["year-month"].toString()),
          int.tryParse(monthRevenueMap["revenue"]["total_sell_price"]
                      .toString()) ==
                  null
              ? 0
              : int.tryParse(
                  monthRevenueMap["revenue"]["total_sell_price"].toString()));
      yearRevenue.add(monthRevenue);
    }

    DashboardInfo storeDashboardInfo = DashboardInfo(
        numberEmployee: dashboardInfo["no_employee"].toString(),
        itemQuantities: dashboardInfo["no_item"].toString(),
        importFee:
            dashboardInfo["import_price"]["total_purchase_price"].toString(),
        sevenDaysRevenue: weekRevenue,
        monthRevenue: monthRevenue,
        yearRevenue: yearRevenue);
    return storeDashboardInfo;
  }

  Future<List<SupplierInfo>> getSupplier({String? name}) async {
    if (await checkToChangeToOfflineMode()) {
      return [];
    }
    if (currentUser == null) {
      return [];
    }
    Map<String, dynamic>? suppliersMap;
    List<SupplierInfo> listSuppliers = [];
    if (name != null) {
      suppliersMap = await api.getSuppliers(
        currentUser!.storeId,
        currentUser!.branchId,
        name: name,
      );
    } else {
      suppliersMap =
          await api.getSuppliers(currentUser!.storeId, currentUser!.branchId);
    }
    if (suppliersMap!["state"] == "success") {
      for (Map<String, dynamic> supplier in suppliersMap["supplier"]) {
        listSuppliers.add(SupplierInfo(
            id: supplier["id"].toString(),
            branchId: supplier["branch_id"].toString(),
            name: supplier["name"].toString(),
            phoneNumber: supplier["phone"].toString(),
            email: supplier["email"].toString(),
            address: supplier["address"].toString(),
            deleted: supplier["deleted"].toString()));
      }
      return listSuppliers;
    } else {
      for (String error in suppliersMap["errors"]) {
        debugPrint(error);
        return [];
      }
    }
    return [];
  }

  Future<List<CategoryInfo>> getCategory() async {
    if (await checkToChangeToOfflineMode()) {
      return [];
    }
    if (currentUser == null) {
      return [];
    }
    if (!currentUser!.roles.contains("purchasing")&&!currentUser!.roles.contains("selling")) {
      return [];
    }
    if (!networkAvailable) {
      return this.storedCategoryInfo;
    }
    Map<String, dynamic> categoryMap =
        await api.getCategory(currentUser!.storeId, currentUser!.branchId);
    if (categoryMap["state"] == "success") {
      List<CategoryInfo> categories = <CategoryInfo>[];
      for (Map<String, dynamic> category in categoryMap["category"]) {
        CategoryInfo tempCategory = CategoryInfo(
            id: category["id"].toString(),
            name: category["name"].toString(),
            deleted: category["deleted"].toString(),
            storeId: category["store_id"].toString(),
            createdAt: category["created_at"].toString(),
            updatedAt: category["updated_at"].toString(),
        pointRatio: category["point_ratio"].toString());
        categories.add(tempCategory);
      }
      return categories;
    } else {
      for (String error in categoryMap["errors"]) {
        debugPrint(error);
      }
      return [];
    }
  }

  Future<List<CustomerInfo>> getCustomer({int? customerId}) async {
    if (await checkToChangeToOfflineMode()) {
      return [];
    }
    if (currentUser == null) {
      return [];
    }
    if (!currentUser!.roles.contains("selling") &&
        !currentUser!.roles.contains("reporting")) {
      return [];
    }
    if (!networkAvailable) {
      return this.storedCustomerInfo;
    }
    Map<String, dynamic> searchMap = {};
    if (customerId != null) {
      searchMap["customer_id"] = customerId;
    }
    Map<String, dynamic> customersMap = await (api.getAllCustomers(
            currentUser!.storeId, currentUser!.branchId, searchMap: searchMap))
        as Map<String, dynamic>;
    List<CustomerInfo> customers = [];
    if (customersMap["state"] == "success") {
      for (Map<String, dynamic> customer in customersMap["customer"]) {
        CustomerInfo tempCustomer = CustomerInfo(
            id: customer["id"].toString(),
            storeId: customer["branch_id"].toString(),
            name: customer["name"].toString(),
            phoneNumber: customer["phone"].toString(),
            customerPoint: customer["point"].toString(),
            address: customer["address"].toString(),
            gender: customer["gender"].toString(),
            email: customer["email"].toString(),
            createdDate: customer["created_datetime"].toString(),
            customerCode: customer["customer_code"].toString(),
            dateOfBirth: customer["date_of_birth"].toString(),
            deleted: customer["deleted"].toString());
        customers.add(tempCustomer);
      }
      return customers;
    } else {
      debugPrint(customersMap.toString());
      return [];
    }
  }

  Future<List<EmployeeInfo>> getEmployee() async {
    if (await checkToChangeToOfflineMode()) {
      return [];
    }
    if (currentUser == null) {
      return [];
    }
    Map<String, dynamic> employeesMap = await (api.getAllEmployees(
        currentUser!.storeId, currentUser!.branchId)) as Map<String, dynamic>;
    List<EmployeeInfo> employees = [];
    debugPrint(employeesMap.toString());
    for (Map<String, dynamic> employee in employeesMap["employee_list"]) {
      EmployeeInfo tempCustomer = EmployeeInfo(
          userId: employee["user_id"].toString(),
          branchId: employee["branch_id"].toString(),
          userName: employee["username"].toString(),
          name: employee["name"].toString(),
          phone: employee["phone"].toString(),
          gender: employee["gender"].toString(),
          email: employee["email"].toString(),
          dateOfBirth: employee["date_of_birth"].toString(),
          status: employee["status"].toString(),
          roles:
              employee["roles"] != null ? List.from(employee["roles"]) : null);
      employees.add(tempCustomer);
    }
    return employees;
  }

  Future<PurchasedSheetPagination?> getImportInvoices(
      {required int page,
      required String orderBy,
      required String order,
      String? keyword,
      int? totalMoneyFrom,
      int? totalMoneyTo,
      DateTime? createdFrom,
      DateTime? createdTo,
      int? purchasedSheetId,
      int? supplierId,
      int? purchaserId}) async {
    if (await checkToChangeToOfflineMode()) {
      return null;
    }
    ;
    if (currentUser == null) {
      return null;
    }
    if (!currentUser!.roles.contains("purchasing")) {
      return null;
    }
    Map<String, dynamic> filterMap = {"order_by": orderBy, "order": order};
    filterMap["page"] = page;
    if (totalMoneyFrom != null) {
      filterMap["total_purchase_price_from"] = totalMoneyFrom;
    }
    if (totalMoneyTo != null) {
      filterMap["total_purchase_price_to"] = totalMoneyTo;
    }
    if (createdFrom != null) {
      filterMap["created_datetime_from"] =
          DateFormat("yyyy-MM-dd").format(createdFrom);
    }
    if (createdTo != null) {
      filterMap["created_datetime_to"] =
          DateFormat("yyyy-MM-dd").format(createdTo);
    }
    if (purchasedSheetId != null) {
      filterMap["purchased_sheet_id"] = purchasedSheetId;
    }
    if (supplierId != null) {
      filterMap["supplier_id"] = supplierId;
    }
    if (purchaserId != null) {
      filterMap["purchaser_id"] = purchaserId;
    }
    if (keyword != null) {
      filterMap["keyword"] = keyword;
    }
    Map<String, dynamic> importInvoiceMap = await (api.getPurchasedSheet(
            currentUser!.storeId, currentUser!.branchId, searchMap: filterMap))
        as Map<String, dynamic>;
    debugPrint(importInvoiceMap.toString());
    List<PurchasedSheetInfo> importInvoices = [];
    for (Map<String, dynamic> importInvoice
        in importInvoiceMap["purchased_sheet"]["data"]) {
      PurchasedSheetInfo tempInfo = PurchasedSheetInfo(
          purchasedSheetId: importInvoice["purchased_sheet_id"].toString(),
          branchId: importInvoice["branch_id"].toString(),
          purchaserId: importInvoice["purchaser_id"].toString(),
          purchaserName: importInvoice["purchaser_name"].toString(),
          supplierId: importInvoice["supplier_id"].toString(),
          supplierName: importInvoice["supplier_name"].toString(),
          supplierPhone: importInvoice["supplier_phone"].toString(),
          totalPurchasePrice: importInvoice["total_purchase_price"].toString(),
          discount: importInvoice["discount"].toString(),
          deliverName: importInvoice["deliver_name"].toString(),
          deliveryDate: importInvoice["delivery_datetime"].toString());
      importInvoices.add(tempInfo);
    }
    PurchasedSheetPagination importPage = PurchasedSheetPagination(
        int.tryParse(importInvoiceMap["purchased_sheet"]["current_page"]
                    .toString()) !=
                null
            ? int.parse(
                importInvoiceMap["purchased_sheet"]["current_page"].toString())
            : -1,
        int.tryParse(importInvoiceMap["purchased_sheet"]["last_page"]
                    .toString()) !=
                null
            ? int.parse(
                importInvoiceMap["purchased_sheet"]["last_page"].toString())
            : -1,
        int.tryParse(importInvoiceMap["purchased_sheet"]["per_page"].toString()) !=
                null
            ? int.parse(
                importInvoiceMap["purchased_sheet"]["per_page"].toString())
            : -1,
        importInvoices);
    return importPage;
  }

  Future<DetailPurchasedSheetInfo?> getDetailPurchasedSheet(
      PurchasedSheetInfo importInvoiceInfo) async {
    if (await checkToChangeToOfflineMode()) {
      return null;
    }
    ;
    if (currentUser == null) {
      return null;
    }
    if (!currentUser!.roles.contains("purchasing")) {
      return null;
    }
    Map<String, dynamic> importInvoiceMap = await (api
        .getOnePurchasedSheet(currentUser!.storeId, currentUser!.branchId, {
      "purchased_sheet_id": importInvoiceInfo.purchasedSheetId,
    })) as Map<String, dynamic>;
    debugPrint(importInvoiceMap.toString());
    List<PurchasedItem> purchasedItems = [];
    for (Map<String, dynamic> purchasedItem
        in importInvoiceMap["purchased_sheet"]) {
      PurchasedItem tempInfo = PurchasedItem(
        purchasedSheetId: purchasedItem["purchased_sheet_id"].toString(),
        purchasedItemId: purchasedItem["purchased_item_id"].toString(),
        purchasePrice: purchasedItem["purchase_price"].toString(),
        quantity: purchasedItem["quantity"].toString(),
        itemId: purchasedItem["item_id"].toString(),
        name: purchasedItem["name"].toString(),
        imageUrl: purchasedItem["image_url"].toString(),
      );
      purchasedItems.add(tempInfo);
    }
    debugPrint("Done get one import");
    return DetailPurchasedSheetInfo(importInvoiceInfo, purchasedItems);
  }

  Future<InvoicePagination?> getInvoices(
      {required int page,
      required String orderBy,
      required String order,
      int? invoiceId,
      int? totalMoneyFrom,
      int? totalMoneyTo,
      DateTime? createdFrom,
      DateTime? createdTo,
      int? customerId,
      String? keyword,
      int? sellerId}) async {
    if (await checkToChangeToOfflineMode()) {
      return null;
    }
    ;
    if (currentUser == null) {
      return null;
    }
    if (!currentUser!.roles.contains("selling")) {
      return null;
    }
    Map<String, dynamic> filterMap = {"order_by": orderBy, "order": order};
    filterMap["page"] = page;
    if (totalMoneyFrom != null) {
      filterMap["total_sell_price_from"] = totalMoneyFrom;
    }
    if (totalMoneyTo != null) {
      filterMap["total_sell_price_to"] = totalMoneyTo;
    }
    if (createdFrom != null) {
      filterMap["created_datetime_from"] =
          DateFormat("yyyy-MM-dd").format(createdFrom);
    }
    if (createdTo != null) {
      filterMap["created_datetime_to"] =
          DateFormat("yyyy-MM-dd").format(createdTo);
    }
    if (invoiceId != null) {
      filterMap["invoice_id"] = invoiceId;
    }
    if (customerId != null) {
      filterMap["customer_id"] = customerId;
    }
    if (sellerId != null) {
      filterMap["seller_id"] = sellerId;
    }
    if (keyword != null) {
      filterMap["keyword"] = keyword;
    }
    Map<String, dynamic> invoiceMap = await (api.getInvoice(
            currentUser!.storeId, currentUser!.branchId, filterMap: filterMap))
        as Map<String, dynamic>;
    debugPrint(invoiceMap.toString());
    List<InvoiceReceivedWhenGet> invoices = [];
    if(invoiceMap["invoice"]==null){
      return null;
    }
    for (Map<String, dynamic> invoice in invoiceMap["invoice"]["data"]) {
      InvoiceReceivedWhenGet tempInfo = InvoiceReceivedWhenGet(
          invoiceId: invoice["invoice_id"].toString(),
          branchId: invoice["branch_id"].toString(),
          sellerId: invoice["seller_id"].toString(),
          sellerName: invoice["seller_name"].toString(),
          customerId: invoice["customer_id"].toString(),
          customerName: invoice["customer_name"].toString(),
          customerPhone: invoice["customer_phone"].toString(),
          totalSellPrice: invoice["total_sell_price"].toString(),
          discount: invoice["discount"].toString(),
          createdDatetime: invoice["created_datetime"].toString(),
          status: invoice["status"].toString());
      invoices.add(tempInfo);
    }
    if (invoices.isEmpty) {
      return null;
    }
    InvoicePagination invoicePage = InvoicePagination(
        int.tryParse(invoiceMap["invoice"]["current_page"].toString()) != null
            ? int.parse(invoiceMap["invoice"]["current_page"].toString())
            : -1,
        int.tryParse(invoiceMap["invoice"]["last_page"].toString()) != null
            ? int.parse(invoiceMap["invoice"]["last_page"].toString())
            : -1,
        int.tryParse(invoiceMap["invoice"]["per_page"].toString()) != null
            ? int.parse(invoiceMap["invoice"]["per_page"].toString())
            : -1,
        invoices);
    return invoicePage;
  }

  Future<DetailInvoiceInfo?> getDetailInvoice(
      InvoiceReceivedWhenGet invoice) async {
    if (await checkToChangeToOfflineMode()) {
      return null;
    }
    debugPrint("Here");
    if (currentUser == null) {
      return null;
    }
    if (!currentUser!.roles.contains("selling")) {
      return null;
    }
    Map<String, dynamic> importInvoiceMap =
        (await api.getOneInvoice(currentUser!.storeId, currentUser!.branchId, {
      "invoice_id": invoice.invoiceId,
    })) as Map<String, dynamic>;

    List<Item> items = [];
    for (Map<String, dynamic> invoice in importInvoiceMap["invoice"]) {
      Item tempInfo = Item(
        invoiceId: invoice["invoice_id"].toString(),
        invoiceItemId: invoice["invoice_item_id"].toString(),
        sellPrice: invoice["sell_price"].toString(),
        quantity: invoice["quantity"].toString(),
        itemId: invoice["item_id"].toString(),
        name: invoice["name"].toString(),
        imageUrl: invoice["image_url"].toString(),
        pointRatio: invoice["point_ratio"].toString()
      );
      items.add(tempInfo);
    }
    return DetailInvoiceInfo(invoice, items);
  }

  Future<RefundPagination?> getRefundSheets(
      {required int page,
      required String orderBy,
      required String order,
      String? keyword,
      int? totalMoneyFrom,
      int? totalMoneyTo,
      DateTime? createdFrom,
      DateTime? createdTo,
      int? refundSheetId,
      int? invoiceId,
      int? refunderId}) async {
    if (await checkToChangeToOfflineMode()) {
      return null;
    }
    ;
    if (currentUser == null) {
      return null;
    }
    if (!currentUser!.roles.contains("selling")) {
      return null;
    }
    Map<String, dynamic> filterMap = {
      "order_by": orderBy,
      "order": order,
      "page": page
    };
    if (keyword != null) {
      filterMap["keyword"] = keyword;
    }
    if (totalMoneyFrom != null) {
      filterMap["total_refund_price_from"] = totalMoneyFrom;
    }
    if (totalMoneyTo != null) {
      filterMap["total_refund_price_to"] = totalMoneyTo;
    }
    if (createdFrom != null) {
      filterMap["created_datetime_from"] =
          DateFormat("yyyy-MM-dd").format(createdFrom);
    }
    if (createdTo != null) {
      filterMap["created_datetime_to"] =
          DateFormat("yyyy-MM-dd").format(createdTo);
    }
    if (refundSheetId != null) {
      filterMap["refund_sheet_id"] = refundSheetId;
    }
    if (invoiceId != null) {
      filterMap["invoice_id"] = invoiceId;
    }
    if (refunderId != null) {
      filterMap["refunder_id"] = refunderId;
    }
    Map<String, dynamic> refundSheetsMap = (await api.getRefundSheet(
            currentUser!.storeId, currentUser!.branchId, searchMap: filterMap))
        as Map<String, dynamic>;
    debugPrint(refundSheetsMap.toString());
    if (refundSheetsMap["state"] == "success") {
      List<RefundSheet> refundSheets = [];
      for (Map<String, dynamic> refundSheet in refundSheetsMap["refund_sheet"]
          ["data"]) {
        RefundSheet tempInfo = RefundSheet(
            refundSheetId: refundSheet["refund_sheet_id"].toString(),
            invoiceId: refundSheet["invoice_id"].toString(),
            refunderId: refundSheet["refunder_id"].toString(),
            refunderName: refundSheet["refunder_name"].toString(),
            customerId: refundSheet["customer_id"].toString(),
            customerName: refundSheet["customer_name"].toString(),
            customerPhone: refundSheet["customers_phone"].toString(),
            totalRefundPrice: refundSheet["total_refund_price"].toString(),
            reason: refundSheet["reason"].toString(),
            createdDatetime: refundSheet["created_datetime"].toString());
        refundSheets.add(tempInfo);
      }
      RefundPagination refundPage = RefundPagination(
          int.tryParse(refundSheetsMap["refund_sheet"]["current_page"]
                      .toString()) !=
                  null
              ? int.parse(
                  refundSheetsMap["refund_sheet"]["current_page"].toString())
              : -1,
          int.tryParse(refundSheetsMap["refund_sheet"]["last_page"]
                      .toString()) !=
                  null
              ? int.parse(
                  refundSheetsMap["refund_sheet"]["last_page"].toString())
              : -1,
          int.tryParse(
                      refundSheetsMap["refund_sheet"]["per_page"].toString()) !=
                  null
              ? int.parse(
                  refundSheetsMap["refund_sheet"]["per_page"].toString())
              : -1,
          refundSheets);
      return refundPage;
    } else {
      debugPrint(refundSheetsMap["error"].toString());
      return null;
    }
  }

  Future<ReturnPurchasedSheetPagination?> getReturnPurchasedSheet(
      {required int page,
        required String orderBy,
        required String order,
        String? keyword,
        int? totalMoneyFrom,
        int? totalMoneyTo,
        DateTime? createdFrom,
        DateTime? createdTo,
        int? returnPurchasedSheetId,
        int? purchasedSheetId,
        int? returnerId}) async {
    debugPrint("Enter get return purchased sheet service");
    if (await checkToChangeToOfflineMode()) {
      return null;
    }
    if (currentUser == null) {
      return null;
    }
    if (!currentUser!.roles.contains("purchasing")) {
      return null;
    }

    Map<String, dynamic> filterMap = {
      "order_by": orderBy,
      "order": order,
      "page": page
    };
    if (keyword != null) {
      filterMap["keyword"] = keyword;
    }
    if (totalMoneyFrom != null) {
      filterMap["total_return_money_from"] = totalMoneyFrom;
    }
    if (totalMoneyTo != null) {
      filterMap["total_return_money_to"] = totalMoneyTo;
    }
    if (createdFrom != null) {
      filterMap["created_datetime_from"] =
          DateFormat("yyyy-MM-dd").format(createdFrom);
    }
    if (createdTo != null) {
      filterMap["created_datetime_to"] =
          DateFormat("yyyy-MM-dd").format(createdTo);
    }
    if (returnPurchasedSheetId != null) {
      filterMap["return_purchased_sheet_id"] = returnPurchasedSheetId;
    }
    if (purchasedSheetId != null) {
      filterMap["purchased_sheet_id"] = purchasedSheetId;
    }
    if (returnerId != null) {
      filterMap["returner_id"] = returnerId;
    }
    debugPrint("Call get return purchased sheet");
    Map<String, dynamic> returnPurchasedSheet = (await api.getReturnPurchasedSheet(
        currentUser!.storeId, currentUser!.branchId, searchMap: filterMap))
    as Map<String, dynamic>;
    debugPrint(returnPurchasedSheet.toString());
    if (returnPurchasedSheet["state"] == "success") {
      List<ReturnPurchasedSheetInfo> returnPurchasedSheets = [];
      for (Map<String, dynamic> refundSheet in returnPurchasedSheet["return_purchased_sheet"]
      ["data"]) {
        ReturnPurchasedSheetInfo tempInfo = ReturnPurchasedSheetInfo(
            returnPurchasedSheetId: refundSheet["return_purchased_sheet_id"].toString(),
            purchasedSheetId: refundSheet["purchased_sheet_id"].toString(),
            returnerId: refundSheet["returner_id"].toString(),
            returnerName: refundSheet["returner_name"].toString(),
            supplierId: refundSheet["supplier_id"].toString(),
            supplierName: refundSheet["supplier_name"].toString(),
            totalReturnMoney: refundSheet["total_return_money"].toString(),
            createdDateTime: refundSheet["created_datetime"].toString());
        returnPurchasedSheets.add(tempInfo);
      }
      debugPrint("End parse return sheet");
      ReturnPurchasedSheetPagination refundPage = ReturnPurchasedSheetPagination(
          int.tryParse(returnPurchasedSheet["return_purchased_sheet"]["current_page"]
              .toString()) !=
              null
              ? int.parse(
              returnPurchasedSheet["return_purchased_sheet"]["current_page"].toString())
              : -1,
          int.tryParse(returnPurchasedSheet["return_purchased_sheet"]["last_page"]
              .toString()) !=
              null
              ? int.parse(
              returnPurchasedSheet["return_purchased_sheet"]["last_page"].toString())
              : -1,
          int.tryParse(
              returnPurchasedSheet["return_purchased_sheet"]["per_page"].toString()) !=
              null
              ? int.parse(
              returnPurchasedSheet["return_purchased_sheet"]["per_page"].toString())
              : -1,
          returnPurchasedSheets);
      debugPrint("End parse return sheet pagination");
      return refundPage;
    } else {
      debugPrint(returnPurchasedSheet["error"].toString());
      return null;
    }
  }

  Future<DetailRefundSheet?> getDetailRefundSheet(
      RefundSheet refundSheet) async {
    if (await checkToChangeToOfflineMode()) {
      return null;
    }
    ;
    if (currentUser == null) {
      return null;
    }
    if (!currentUser!.roles.contains("selling")) {
      return null;
    }
    Map<String, dynamic> importInvoiceMap = (await api
        .getOneRefundSheet(currentUser!.storeId, currentUser!.branchId, {
      "refund_sheet_id": refundSheet.refundSheetId,
    })) as Map<String, dynamic>;

    List<RefundItem> items = [];
    for (Map<String, dynamic> invoice in importInvoiceMap["refund_sheet"]) {
      RefundItem tempInfo = RefundItem(
        refundSheetId: invoice["refund_sheet_id"].toString(),
        refundItemId: invoice["refund_item_id"].toString(),
        itemId: invoice["item_id"].toString(),
        name: invoice["name"].toString(),
        sellPrice: invoice["sell_price"].toString(),
        quantity: invoice["quantity"].toString(),
        imageUrl: invoice["image_url"].toString(),
      );
      items.add(tempInfo);
    }
    return DetailRefundSheet(refundSheet, items);
  }

  Future<DetailReturnPurchasedSheetInfo?> getDetailReturnPurchasedSheet(
      ReturnPurchasedSheetInfo returnPurchasedSheet) async {
    if (await checkToChangeToOfflineMode()) {
      return null;
    }
    if (currentUser == null) {
      return null;
    }
    if (!currentUser!.roles.contains("purchasing")) {
      return null;
    }
    Map<String, dynamic> returnPurchasedSheetMap = (await api
        .getOneReturnPurchasedSheet(currentUser!.storeId, currentUser!.branchId, {
      "return_purchased_sheet_id": returnPurchasedSheet.returnPurchasedSheetId,
    })) as Map<String, dynamic>;
    debugPrint(returnPurchasedSheetMap.toString());
    List<ReturnItemInfo> items = [];
    for (Map<String, dynamic> invoice in returnPurchasedSheetMap["return_purchased_sheet"]) {
      ReturnItemInfo tempInfo = ReturnItemInfo(
        returnPurchasedSheetId: invoice["return_purchased_sheet_id"].toString(),
        returnPurchasedItemID: invoice["return_purchased_item_id"].toString(),
        itemId: invoice["item_id"].toString(),
        name: invoice["name"].toString(),
        oldPurchasedPrice: invoice["old_purchased_price"].toString(),
        oldQuantity: invoice["old_quantity"].toString(),
        imageUrl: invoice["image_url"].toString(),
      );
      items.add(tempInfo);
    }
    return DetailReturnPurchasedSheetInfo(returnPurchasedSheet:returnPurchasedSheet, returnItems: items);
  }

  Future<List<ShiftInfo>> getShifts({int? shiftId}) async {
    if (await checkToChangeToOfflineMode()) {
      return [];
    }
    if (currentUser == null) {
      return [];
    }
    if (!currentUser!.roles.contains("managing")) {
      return [];
    }
    Map<String, dynamic> shiftMap = (await api.getShifts(
        currentUser!.storeId, currentUser!.branchId, searchMap: {
      (shiftId != null ? "shift_id" : "not_have_shift_id"): shiftId
    })) as Map<String, dynamic>;

    List<ShiftInfo> shifts = [];
    for (Map<String, dynamic> shift in shiftMap["shift"]) {
      ShiftInfo tempInfo = ShiftInfo(
        shiftId: shift["shift_id"].toString(),
        name: shift["name"].toString(),
        startTime: shift["start_time"].toString(),
        endTime: shift["end_time"].toString(),
        monday: shift["monday"].toString(),
        tuesday: shift["tuesday"].toString(),
        wednesday: shift["wednesday"].toString(),
        thursday: shift["thursday"].toString(),
        friday: shift["friday"].toString(),
        saturday: shift["saturday"].toString(),
        sunday: shift["sunday"].toString(),
        startDate: shift["start_date"].toString(),
        endDate: shift["end_date"].toString(),
      );
      shifts.add(tempInfo);
    }
    return shifts;
  }

  Future<List<ScheduleInfo>> getSchedules({int? shiftId, int? userId}) async {
    if (await checkToChangeToOfflineMode()) {
      return [];
    }
    if (currentUser == null) {
      return [];
    }
    if (!currentUser!.roles.contains("managing")) {
      return [];
    }
    Map<String, dynamic> scheduleMap = (await api
        .getSchedules(currentUser!.storeId, currentUser!.branchId, searchMap: {
      (shiftId != null ? "shift_id" : "not_have_shift_id"): shiftId,
      (userId != null ? "user_id" : "not_have_user_id"): userId
    })) as Map<String, dynamic>;
    debugPrint(scheduleMap.toString());
    List<ScheduleInfo> schedules = [];
    debugPrint(scheduleMap.toString());
    for (Map<String, dynamic> schedule in scheduleMap["schedule_list"]) {
      ScheduleInfo tempInfo = ScheduleInfo(
        shiftId: schedule["shifts_id"].toString(),
        scheduleId: schedule["schedule_id"].toString(),
        scheduleStartDate: schedule["schedule_start_date"].toString(),
        scheduleEndDate: schedule["schedule_end_date"].toString(),
        userId: schedule["user_id"].toString(),
        name: schedule["name"].toString(),
        status: schedule["status"].toString(),
      );
      schedules.add(tempInfo);
    }
    return schedules;
  }

  Future<List<AttendanceInfo>> getAttendances(DateTime date,
      {bool? haveAttended, int? scheduleId}) async {
    if (await checkToChangeToOfflineMode()) {
      return [];
    }
    if (currentUser == null) {
      return [];
    }
    if (!currentUser!.roles.contains("managing")) {
      return [];
    }
    Map<String,dynamic> searchMap = {
      "date": DateFormat("yyyy-MM-dd").format(date),
    };
    if(scheduleId!=null){
      searchMap["schedule_id"]=scheduleId;
    }
    if(haveAttended!=null){
      searchMap["have_attended"]=haveAttended?1:0;
    }
    Map<String, dynamic> attendanceMap = (await api.getAttendances(
        currentUser!.storeId, currentUser!.branchId,
        searchMap: searchMap)) as Map<String, dynamic>;

    List<AttendanceInfo> attendances = [];
    for (Map<String, dynamic> attendance in attendanceMap["attendance_list"]) {
      AttendanceInfo tempInfo = AttendanceInfo(
        shiftId: attendance["shift_id"].toString(),
        scheduleId: attendance["schedule_id"].toString(),
        userId: attendance["user_id"].toString(),
        name: attendance["name"].toString(),
        date: attendance["date"].toString(),
      );
      attendances.add(tempInfo);
    }
    return attendances;
  }

  Future<List<Map<String, dynamic>>> getAttendancesFromDateToDate(
      DateTime fromDate, DateTime toDate,
      {int? shiftId, int? scheduleId, int? userId}) async {
    if (await checkToChangeToOfflineMode()) {
      return [];
    }
    if (currentUser == null) {
      return [];
    }
    if (!currentUser!.roles.contains("managing")) {
      return [];
    }
    Map<String,dynamic> searchMap = {
      "from_date": DateFormat("yyyy-MM-dd").format(fromDate),
      "to_date": DateFormat("yyyy-MM-dd").format(toDate),
    };
    if(scheduleId!=null){
      searchMap["schedule_id"]=scheduleId;
    }
    if(shiftId!=null){
      searchMap["shift_id"]=shiftId;
    }
    if(userId!=null){
      searchMap["user_id"]=userId;
    }
    Map<String, dynamic> attendanceMap = (await api
        .getAttendancesFromDateToDate(
            currentUser!.storeId, currentUser!.branchId,
            searchMap: searchMap)) as Map<String, dynamic>;
    debugPrint(attendanceMap.toString());
    List<Map<String, dynamic>> attendances = [];
    for (Map<String, dynamic> attendance in attendanceMap["list"]) {
      for (Map<String, dynamic> data in attendance["data"]) {
        AttendanceInfo tempInfo = AttendanceInfo(
          shiftId: data["shift_id"].toString(),
          scheduleId: data["schedule_id"].toString(),
          userId: data["user_id"].toString(),
          name: data["name"].toString(),
          date: data["date"].toString(),
        );
        attendances.add({"date": attendance["date"], "attendance": tempInfo});
        break;
      }
    }
    return attendances;
  }

  Future<ChartInfo?> getCharts(DateTime fromDate, DateTime toDate,
      {required String unit,
      int revenue = 1,
      int profit = 1,
      int capital = 1,
      int purchase = 1}) async {
    if (await checkToChangeToOfflineMode()) {
      return null;
    }
    ;
    if (currentUser == null) {
      return null;
    }
    if (!currentUser!.roles.contains("reporting")) {
      return null;
    }
    Map<String, dynamic> searchMap = {
      "from_date": DateFormat("yyyy-MM-dd").format(fromDate),
      "to_date": DateFormat("yyyy-MM-dd").format(toDate),
      "unit": unit,
      "revenue": revenue,
      "profit": profit,
      "capital": capital,
      "purchase": purchase
    };
    debugPrint(searchMap.toString());
    Map<String, dynamic> chartMap = await api.getReportRevenue(
        this.currentUser!.storeId!, this.currentUser!.branchId!,
        searchMap: searchMap);
    debugPrint(chartMap.toString());
    if (chartMap["statusCode"] == 200) {
      List<int> listRevenue = [];
      for (var revenue in chartMap["revenue"]) {
        listRevenue.add(int.tryParse(revenue.toString()) == null
            ? 0
            : int.parse(revenue.toString()));
      }
      List<int> listProfit = [];
      for (var profit in chartMap["profit"]) {
        listProfit.add(int.tryParse(profit.toString()) == null
            ? 0
            : int.parse(profit.toString()));
      }
      List<int> listCapital = [];
      for (var capital in chartMap["capital"]) {
        listCapital.add(int.tryParse(capital.toString()) == null
            ? 0
            : int.parse(capital.toString()));
      }
      List<int> listPurchase = [];
      for (var purchase in chartMap["purchase"]) {
        listPurchase.add(int.tryParse(purchase.toString()) == null
            ? 0
            : int.parse(purchase.toString()));
      }
      List<ItemInfo> noPurchasedItems = [];
      for(var itemMap in chartMap["no_purchased_price_items"]){
        ItemInfo item = ItemInfo(
            itemId: itemMap["item_id"].toString(),
            itemName: itemMap["item_name"].toString(),
            barCode: itemMap["bar_code"].toString(),
            imageUrl: itemMap["image_url"].toString(),
            createdDate: itemMap["created_datetime"].toString(),
            categoryId: itemMap["category_id"].toString(),
            categoryName: itemMap["category_name"].toString(),
            quantity: itemMap["quantity"].toString(),
            purchasePrice: itemMap["purchase_price"],
            priceId: itemMap["price_id"].toString(),
            sellPrice: itemMap["sell_price"].toString(),
            pointRatio: itemMap["point_ratio"].toString()
        );
        noPurchasedItems.add(item);
      }
      ChartInfo chartInfo = ChartInfo(fromDate, toDate, unit, listRevenue,
          listProfit, listCapital, listPurchase,noPurchasedItems);
      return chartInfo;
    } else {
      debugPrint("Error occured!!!");
      debugPrint(chartMap.toString());
      return null;
    }
  }

  Future<itemReport.ItemReportInfo?> getReportItems({
    required int maxLimitItems,
    required int categoryId,
    DateTime? fromDate,
    DateTime? toDate,
  }) async {
    if (await checkToChangeToOfflineMode()) {
      return null;
    }
    ;
    if (currentUser == null) {
      return null;
    }
    if (!currentUser!.roles.contains("reporting")) {
      return null;
    }
    Map<String, dynamic> searchMap = {
      "limit":maxLimitItems,
      "category_id":categoryId
    };
    if (fromDate != null) {
      searchMap["from_date"] = DateFormat("yyyy-MM-dd").format(fromDate);
    }
    if (toDate != null) {
      searchMap["to_date"] = DateFormat("yyyy-MM-dd").format(toDate);
    }
    debugPrint(searchMap.toString());
    Map<String, dynamic> chartMap = await api.getReportItems(
        this.currentUser!.storeId!, this.currentUser!.branchId!,
        searchMap: searchMap);
    debugPrint(chartMap.toString());
    if (chartMap["statusCode"] == 200) {
      List<itemReport.Item> listTop3SellPriceItem = [];
      for (var item in chartMap["top_total_sell_price_item"]) {
        listTop3SellPriceItem.add(itemReport.Item(
            id: item["id"].toString(),
            name: item["name"].toString(),
            imageUrl: item["image_url"].toString(),
            totalQuantity: item["total_quantity"].toString(),
            totalSellPrice: item["total_sell_price"].toString()));
      }
      List<itemReport.Item> listTop3SoldQuantityItem = [];
      if(chartMap["top_sold_quantity_item"].length==0){
        return null;
      }
      for (var item in chartMap["top_sold_quantity_item"]) {
        listTop3SoldQuantityItem.add(itemReport.Item(id: item["id"].toString(),
            name: item["name"].toString(),
            imageUrl: item["image_url"].toString(),
            totalQuantity: item["total_quantity"].toString(),
            totalSellPrice: item["total_sell_price"].toString()));
      }
      itemReport.ItemReportInfo itemReportInfo = itemReport.ItemReportInfo(top3TotalSellPriceItems: listTop3SellPriceItem, top3SoldQuantityItems: listTop3SoldQuantityItem,
          totalSellPrice: chartMap["total_all"][0]["total_sell_price"], totalSellQuantity: chartMap["total_all"][0]["total_quantity"]);
      return itemReportInfo;
    } else {
      debugPrint("Error occured!!!");
      debugPrint(chartMap.toString());
      return null;
    }
  }

  Future<categoryReport.CategoryReportInfo?> getReportCategories({
    DateTime? fromDate,
    DateTime? toDate,
  }) async {
    if (await checkToChangeToOfflineMode()) {
      return null;
    }
    if (currentUser == null) {
      return null;
    }
    if (!currentUser!.roles.contains("reporting")) {
      return null;
    }
    Map<String, dynamic> searchMap = {};
    if (fromDate != null) {
      searchMap["from_date"] = DateFormat("yyyy-MM-dd").format(fromDate);
    }
    if (toDate != null) {
      searchMap["to_date"] = DateFormat("yyyy-MM-dd").format(toDate);
    }
    debugPrint(searchMap.toString());
    Map<String, dynamic> chartMap = await api.getReportCategories(
        this.currentUser!.storeId!, this.currentUser!.branchId!,
        searchMap: searchMap);
    debugPrint(chartMap.toString());
    if (chartMap["statusCode"] == 200) {
      List<categoryReport.Category> listTopTotalSellPRiceCategory = [];
      for (var item in chartMap["category_report_info"]) {
        debugPrint(item.toString());
        listTopTotalSellPRiceCategory.add(categoryReport.Category(
            id: item["id"].toString(),
            name: item["name"].toString(),
            totalQuantity: item["total_quantity"].toString(),
            totalSellPrice: item["total_sell_price"].toString()));
      }
      categoryReport.CategoryReportInfo categoryReportINfo = categoryReport.CategoryReportInfo(totalSellPriceCategory: listTopTotalSellPRiceCategory);
      return categoryReportINfo;
    } else {
      debugPrint("Error occured!!!");
      debugPrint(chartMap.toString());
      return null;
    }
  }

  Future<customerReport.CustomerReportInfo?> getReportCustomer({
    DateTime? fromDate,
    DateTime? toDate,
  }) async {
    if (await checkToChangeToOfflineMode()) {
      return null;
    }
    if (currentUser == null) {
      return null;
    }
    if (!currentUser!.roles.contains("reporting")) {
      return null;
    }
    Map<String, dynamic> searchMap = {};
    if (fromDate != null) {
      searchMap["from_date"] = DateFormat("yyyy-MM-dd").format(fromDate);
    }
    if (toDate != null) {
      searchMap["to_date"] = DateFormat("yyyy-MM-dd").format(toDate);
    }
    debugPrint(searchMap.toString());
    Map<String, dynamic> chartMap = await api.getReportCustomers(
        this.currentUser!.storeId!, this.currentUser!.branchId!,
        searchMap: searchMap);
    debugPrint(chartMap.toString());
    if (chartMap["statusCode"] == 200) {
      List<customerReport.Customer> listCustomer = [];
      for (var item in chartMap["top_customer"]) {
        listCustomer.add(customerReport.Customer(
            name: item["name"].toString(),
        totalBuyPrice: item["total_buy_price"]));
      }
      customerReport.CustomerReportInfo customerReportInfo= customerReport.CustomerReportInfo(listCustomer);
      return customerReportInfo;
    } else {
      debugPrint("Error occured!!!");
      debugPrint(chartMap.toString());
      return null;
    }
  }

  Future<supplierReport.SupplierReportInfo?> getReportSupplier({
    DateTime? fromDate,
    DateTime? toDate,
  }) async {
    if (await checkToChangeToOfflineMode()) {
      return null;
    }
    if (currentUser == null) {
      return null;
    }
    if (!currentUser!.roles.contains("reporting")) {
      return null;
    }
    Map<String, dynamic> searchMap = {};
    if (fromDate != null) {
      searchMap["from_date"] = DateFormat("yyyy-MM-dd").format(fromDate);
    }
    if (toDate != null) {
      searchMap["to_date"] = DateFormat("yyyy-MM-dd").format(toDate);
    }
    debugPrint(searchMap.toString());
    Map<String, dynamic> chartMap = await api.getReportSupplier(
        this.currentUser!.storeId!, this.currentUser!.branchId!,
        searchMap: searchMap);
    debugPrint(chartMap.toString());
    if (chartMap["statusCode"] == 200) {
      List<supplierReport.Supplier> listSupplier = [];
      for (var item in chartMap["top_supplier"]) {
        listSupplier.add(supplierReport.Supplier(
            name: item["name"].toString(),
            totalPurchasePrice: item["total_purchase_price"]));
      }
      supplierReport.SupplierReportInfo customerReportInfo= supplierReport.SupplierReportInfo(listSupplier);
      return customerReportInfo;
    } else {
      debugPrint("Error occured!!!");
      debugPrint(chartMap.toString());
      return null;
    }
  }

  Future<PriceHistory?> getPriceHistory({
    required ItemInfo item
  }) async {
    if (await checkToChangeToOfflineMode()) {
      return null;
    }
    if (currentUser == null) {
      return null;
    }
    if (!currentUser!.roles.contains("purchasing")) {
      return null;
    }
    Map<String, dynamic> searchMap = {
    "item_id":item.itemId
    };
    debugPrint(searchMap.toString());
    Map<String, dynamic> priceMap = await api.getPriceHistory(
        this.currentUser!.storeId!, this.currentUser!.branchId!,
        searchMap: searchMap);
    debugPrint(priceMap.toString());
    if (priceMap["statusCode"] == 200) {
      List<ItemPrice> itemPrices = [];
      for(var price in priceMap["price_history"]){
        ItemPrice temp = ItemPrice(itemId: price["item_id"].toString(), name: price["name"].toString(), sellPrice: price["sell_price"].toString(),
            changedBy: price["change_by"].toString(), fromDate: price["start_date"].toString(), toDate: price["end_date"].toString());
        itemPrices.add(temp);
      }
      return PriceHistory(item, itemPrices);
    } else {
      debugPrint("Error occured!!!");
      debugPrint(priceMap.toString());
      return null;
    }
  }

  Future<QuantityHistory?> getQuantityHistory({
    ItemInfo? item,
    int? quantityCheckingSheetId
  }) async {
    if (await checkToChangeToOfflineMode()) {
      return null;
    }
    if (currentUser == null) {
      return null;
    }
    if (!currentUser!.roles.contains("purchasing")) {
      return null;
    }
    Map<String, dynamic> searchMap = {};
    ///Dont' use both item id and quantity checking sheet id
    if(item!=null){
      searchMap["item_id"]=item.itemId;
    }
    if(quantityCheckingSheetId!=null){
      searchMap["quant_checking_sheet_id"]=quantityCheckingSheetId;
    }
    debugPrint(searchMap.toString());
    Map<String, dynamic> quantityMap = await api.getQuantityHistory(
        this.currentUser!.storeId!, this.currentUser!.branchId!,
        searchMap: searchMap);
    debugPrint(quantityMap.toString());
    if (quantityMap["statusCode"] == 200) {
      List<ItemQuantityChange> itemQuantityChanges = [];
      for(var quantityChange in quantityMap["quant_change_history"]){
        ItemQuantityChange temp = ItemQuantityChange(quantityChange: quantityChange["changes"].toString(), itemId: quantityChange["item_id"].toString(),oldQuantity: quantityChange["old_quant"].toString(),
            newQuantity: quantityChange["new_quant"].toString(), reason: quantityChange["reason"].toString(),checkerId: quantityChange["checker_id"].toString(), checkerName: quantityChange["checker_name"],
            createdDatetime: quantityChange["create_datetime"].toString(), );
        itemQuantityChanges.add(temp);
      }
      return QuantityHistory(item, itemQuantityChanges);
    } else {
      debugPrint("Error occured!!!");
      debugPrint(quantityMap.toString());
      return null;
    }
  }

  Future<List<ItemInfo>> getItemsWithNoPurchasePrice() async {
    if (await checkToChangeToOfflineMode()) {
      return [];
    }
    if (currentUser == null) {
      return [];
    }
    if (!currentUser!.roles.contains("purchasing")) {
      return [];
    }
    Map<String, dynamic> itemsWithNoPurchasePriceMap = await api.getItemsWithNoPurchasePrice(
        this.currentUser!.storeId!, this.currentUser!.branchId!);
    if (itemsWithNoPurchasePriceMap["statusCode"] == 200) {
      if(itemsWithNoPurchasePriceMap["state"]=="success"){
        List<ItemInfo> listItemsWithNoPurchasePrice = [];
        for(var itemMap in itemsWithNoPurchasePriceMap["item"]){
          ItemInfo item = ItemInfo(
              itemId: itemMap["item_id"].toString(),
              itemName: itemMap["item_name"].toString(),
              barCode: itemMap["bar_code"].toString(),
              imageUrl: itemMap["image_url"].toString(),
              createdDate: itemMap["created_datetime"].toString(),
              categoryId: itemMap["category_id"].toString(),
              categoryName: itemMap["category_name"].toString(),
              quantity: itemMap["quantity"].toString(),
              purchasePrice: itemMap["purchase_price"],
              priceId: itemMap["price_id"].toString(),
              sellPrice: itemMap["sell_price"].toString(),
              pointRatio: itemMap["point_ratio"].toString()
          );
          listItemsWithNoPurchasePrice.add(item);
        }
        return listItemsWithNoPurchasePrice;
      }else{
        debugPrint(itemsWithNoPurchasePriceMap["error"].toString());
        return [];
      }
    } else {
      debugPrint("Error occured!!!");
      debugPrint(itemsWithNoPurchasePriceMap.toString());
      return [];
    }
  }

  Future<List<HistoryInfo>> getHistory({int? userId,DateTime? fromDate,DateTime? toDate,String? type}) async {
    if (await checkToChangeToOfflineMode()) {
      return [];
    }
    if (currentUser == null) {
      return [];
    }
    if (currentUser!.userId!=this.currentUser!.storeOwnerId) {
      return [];
    }
    Map<String,dynamic> filterMap={};
    if(userId!=null){
      filterMap["user_id"]=userId;
    }
    if(fromDate!=null){
      filterMap["from_date"]=DateFormat("yyyy-MM-dd").format(fromDate);
    }
    if(toDate!=null){
      filterMap["to_date"]=DateFormat("yyyy-MM-dd").format(toDate);
    }
    if(type!=null){
      filterMap["type"]=type;
    }
    Map<String, dynamic> historysMap = await api.getHistory(
        this.currentUser!.storeId!, this.currentUser!.branchId!,filterMap: filterMap);
    debugPrint("History Map");
    debugPrint(historysMap.toString());
    if (historysMap["statusCode"] == 200) {
      if(historysMap["state"]=="success"){
        List<HistoryInfo> listHistory = [];
        for(var historyItemMap in historysMap["merged_array"]){
          HistoryInfo tempInfo = HistoryInfo(id: historyItemMap["id"].toString(), userName: historyItemMap["user_name"].toString(), createdDateTime: historyItemMap["created_datetime"].toString(),
              action: historyItemMap["action"].toString(), type: historyItemMap["type"].toString());
          listHistory.add(tempInfo);
        }
        return listHistory;
      }else{
        debugPrint(historysMap["error"].toString());
        return [];
      }
    } else {
      debugPrint("Error occured!!!");
      debugPrint(historysMap.toString());
      return [];
    }
  }

  Future<UserInfo?> getUserInfo(UserInfo userInfo)async{
    if(currentUser==null){
      return null;
    }
    Map<String,dynamic> responseMap = await api.getUserInfo(userInfo.storeId!, userInfo.branchId!,userInfo.token!);
    if(responseMap["statusCode"]==500||responseMap["statusCode"]==200){
      return null;
    }
    Map<String,dynamic> userInfoMap = responseMap["user_info"][0];
    if(responseMap["state"]=="success"){
      debugPrint(ServerConfig.projectUrl+userInfoMap["avatar_url"].toString());
      var response = await Dio().get(ServerConfig.projectUrl+userInfoMap["avatar_url"].toString(),options: Options(responseType: ResponseType.bytes)); // <--2
      var documentDirectory = await getApplicationDocumentsDirectory();
      var firstPath = documentDirectory.path + "/avatar";
      String avatarName = userInfoMap["avatar_url"].toString().split("/").last;
      var filePathAndName = documentDirectory.path + '/'+avatarName;
      await Directory(firstPath).create(recursive: true); // <-- 1
      File file2 = new File(filePathAndName);             // <-- 2
      file2.writeAsBytesSync(response.data);
      UserInfo userInfo = UserInfo(
          token: this.currentUser!.token,
          userId: userInfoMap["user_id"].toString(),
          name: userInfoMap["name"].toString(),
          username: userInfoMap["username"].toString(),
          email: userInfoMap["email"].toString(),
          phone: userInfoMap["phone"].toString(),
          gender: userInfoMap["gender"].toString(),
          dateOfBirth: userInfoMap["date_of_birth"].toString(),
          storeId: userInfoMap["store_id"].toString(),
          storeName: userInfoMap["storeName"].toString(),
          storeOwnerId: userInfoMap["store_owner_id"].toString(),
          branchId: userInfoMap["branch_id"].toString(),
          branchName: userInfoMap["branch_name"].toString(),
          branchAddress: userInfoMap["branches_address"].toString(),
          roles: userInfoMap["roles"],
          avatarUrl: userInfoMap["avatar_url"].toString(),
          avatarFile: file2.path,
      stayLoggedIn: this.currentUser!.stayLoggedIn);
      return userInfo;
    }else{
      debugPrint(userInfoMap["errors"].toString());
      return null;
    }
  }
  ///
  /// Edit method
  ///
  ///
  Future<MsgInfoCode?> editProduct(
      {required int categoryId,
      required int itemId,
      required String itemName,
      required String? barCode,
      required int quantity,
      required int sellValue,
        required int purchasePrice,
      required bool deleted,
        required double pointRatio,
      required File? imageFile}) async {
    if (await checkToChangeToOfflineMode()) {
      return null;
    }
    ;
    if (currentUser == null) {
      return null;
    }
    if (!currentUser!.roles.contains("purchasing")) {
      return null;
    }
    Map<String, dynamic> productMap = {
      "category_id": categoryId,
      "item_id": itemId,
      "item_name": itemName,
      "bar_code": barCode,
      "quantity": quantity,
      "sell_price": sellValue,
      "purchase_price":purchasePrice,
      "point_ratio":pointRatio
    };
    debugPrint(productMap.toString());
    productMap["image"] = imageFile != null
        ? await MultipartFile.fromFile(imageFile.path,
            filename: imageFile.path.split('/').last)
        : null;
    var response = await api.editItems(
        productMap, currentUser!.storeId, currentUser!.branchId);
    debugPrint(response.toString());
    if (response["state"] == "success") {
      return MsgInfoCode.actionSuccess;
    } else {
      debugPrint(response["errors"].toString());
      return MsgInfoCode.actionFail;
    }
  }

  Future<MsgInfoCode> editCurrentUser(
      {required String phoneNumber,
      required String name,
      required String? gender,
      required String? email,
      DateTime? dateOfBirth,
      required File avatarFile}) async {
    if (await checkToChangeToOfflineMode()) {
      return MsgInfoCode.actionFail;
    }
    if (currentUser == null) {
      return MsgInfoCode.actionFail;
    }
    if (phoneNumber == "") {
      return MsgInfoCode.actionFail;
    }
    Map<String,dynamic> currentUserMap = {
      "phone": phoneNumber,
      "name": name == "" ? null : name,
      "gender": (gender == "" || gender == null) ? null : gender,
      "email": email == "" ? null : email,
      "date_of_birth": dateOfBirth == null
          ? null
          : DateFormat("yyy-MM-dd").format(dateOfBirth)
    };
    debugPrint(currentUserMap.toString());
    currentUserMap["avatar"]=await MultipartFile.fromFile(avatarFile.path,
        filename: avatarFile.path.split('/').last);
    Map<String, dynamic> returnStatus = await api.editCurrentUser(currentUserMap, currentUser!.storeId, currentUser!.branchId);
    debugPrint(returnStatus.toString());
    if (returnStatus["state"] == "success") {
      return MsgInfoCode.actionSuccess;
    } else {
      debugPrint(returnStatus["errors"].toString());
      return MsgInfoCode.actionFail;
    }
  }

  Future<MsgInfoCode?> editCustomer(
      {required int? id,
      required bool deleted,
      required String phoneNumber,
      required String name,
      required String? address,
      required String? gender,
      required String? email,
      DateTime? dateOfBirth}) async {
    if (await checkToChangeToOfflineMode()) {
      return null;
    }
    ;
    if (currentUser == null) {
      return null;
    }
    if (!currentUser!.roles.contains("selling") &&
        !currentUser!.roles.contains("reporting")) {
      return MsgInfoCode.actionFail;
    }
    if (deleted) {
      Map<String, dynamic> returnStatus = await api.editCustomer(
          {"customer_id": id, "deleted": true},
          currentUser!.storeId,
          currentUser!.branchId);
      debugPrint(returnStatus.toString());
      if (returnStatus["state"] == "success") {
        return MsgInfoCode.actionSuccess;
      } else {
        for (String error in returnStatus["errors"]) {
          debugPrint(error);
        }
        return MsgInfoCode.actionFail;
      }
    } else {
      if (id == null || phoneNumber == null || phoneNumber == "") {
        return MsgInfoCode.actionFail;
      }
      Map<String, dynamic> returnStatus = await api.editCustomer({
        "customer_id": id,
        "deleted": false,
        "phone": phoneNumber,
        "name": name == "" ? null : name,
        "address": address == "" ? null : address,
        "gender": (gender == "" || gender == null) ? null : gender,
        "email": email == "" ? null : email,
        "date_of_birth": dateOfBirth == null
            ? null
            : DateFormat("yyy-MM-dd").format(dateOfBirth)
      }, currentUser!.storeId, currentUser!.branchId);
      if (returnStatus["state"] == "success") {
        return MsgInfoCode.actionSuccess;
      } else {
        debugPrint(returnStatus["errors"].toString());
        return MsgInfoCode.actionFail;
      }
    }
  }

  Future<MsgInfoCode?> editSupplier(
      {required int? id,
      required bool deleted,
      required String phoneNumber,
      required String name,
      required String? address,
      required String? email}) async {
    if (await checkToChangeToOfflineMode()) {
      return null;
    }
    ;
    if (currentUser == null) {
      return null;
    }
    if (!currentUser!.roles.contains("purchasing")) {
      return null;
    }
    if (deleted == null) {
      return MsgInfoCode.actionFail;
    }
    if (deleted) {
      Map<String, dynamic> returnStatus = await api.editSupplier(
          {"supplier_id": id, "deleted": 1},
          currentUser!.storeId,
          currentUser!.branchId);
      if (returnStatus["state"] == "success") {
        return MsgInfoCode.actionSuccess;
      } else {
        for (String error in returnStatus["errors"]) {
          debugPrint(error);
        }
        return MsgInfoCode.actionFail;
      }
    } else {
      if (id == null || phoneNumber == null || phoneNumber == "") {
        return MsgInfoCode.actionFail;
      }
      Map<String, dynamic> returnStatus = await api.editSupplier({
        "supplier_id": id,
        "deleted": 0,
        "phone": phoneNumber,
        "name": name == null ? "" : name,
        "address": address == null ? "" : address,
        "email": email == null ? "" : email
      }, currentUser!.storeId, currentUser!.branchId);
      if (returnStatus["state"] == "success") {
        return MsgInfoCode.actionSuccess;
      } else {
        debugPrint(returnStatus["errors"].toString());
        return MsgInfoCode.actionFail;
      }
    }
  }

  Future<MsgInfoCode?> editCategory({
    required int? id,
    required bool deleted,
    required String name,
    required double pointRatio
  }) async {
    if (await checkToChangeToOfflineMode()) {
      return null;
    }
    ;
    if (currentUser == null) {
      return null;
    }
    if (!currentUser!.roles.contains("purchasing")) {
      return null;
    }
    if (deleted) {
      Map<String, dynamic> returnStatus = await api.editCategory(
          {"category_id": id, "deleted": 1},
          currentUser!.storeId,
          currentUser!.branchId);
      if (returnStatus["state"] == "success") {
        return MsgInfoCode.actionSuccess;
      } else {
        for (String error in returnStatus["errors"]) {
          debugPrint(error);
        }
        return MsgInfoCode.actionFail;
      }
    } else {
      if (id == null || name == null || name == "") {
        return MsgInfoCode.actionFail;
      }

      Map<String, dynamic> returnStatus = await api.editCategory({
        "category_id": id,
        "deleted": 0,
        "name": name,
        "point_ratio":pointRatio
      }, currentUser!.storeId, currentUser!.branchId);
      debugPrint(returnStatus.toString());
      if (returnStatus["state"] == "success") {
        return MsgInfoCode.actionSuccess;
      } else {
        debugPrint(returnStatus["errors"].toString());
        return MsgInfoCode.actionFail;
      }
    }
  }

  Future<MsgInfoCode?> editShift(
      {required int? shiftId,
      required String name,
      required DateTime? startTime,
      required DateTime? endTime,
      required bool? monday,
      required bool? tuesday,
      required bool? wednesday,
      required bool? thursday,
      required bool? friday,
      required bool? saturday,
      required bool? sunday,
      required DateTime? startDate,
      required DateTime? endDate}) async {
    if (await checkToChangeToOfflineMode()) {
      return null;
    }
    ;
    if (currentUser == null) {
      return null;
    }
    if (this.currentUser!.userId != this.currentUser!.storeOwnerId) {
      return null;
    }
    Map<String, dynamic> returnStatus = await api.editShift({
      "shift_id": shiftId,
      "name": name != null ? name : "",
      "start_time": startTime != null
          ? DateFormat("HH:mm:ss").format(startTime)
          : "00:00:00",
      "end_time":
          endTime != null ? DateFormat("HH:mm:ss").format(endTime) : "00:00:00",
      "monday": monday,
      "tuesday": tuesday,
      "wednesday": wednesday,
      "thursday": thursday,
      "friday": friday,
      "saturday": saturday,
      "sunday": sunday,
      "start_date": startDate != null
          ? DateFormat("yyyy-MM-dd").format(startDate)
          : DateTime.fromMicrosecondsSinceEpoch(0),
      "end_date":
          endDate != null ? DateFormat("yyyy-MM-dd").format(endDate) : null,
    }, currentUser!.storeId, currentUser!.branchId);
    debugPrint(returnStatus.toString());
    if (returnStatus["state"] == "success") {
      return MsgInfoCode.actionSuccess;
    } else {
      debugPrint(returnStatus["errors"].toString());
      return MsgInfoCode.actionFail;
    }
  }

  Future<MsgInfoCode?> editBranch({
    required String name,
    required String address,
  }) async {
    if (await checkToChangeToOfflineMode()) {
      return null;
    }
    ;
    if (currentUser == null) {
      return null;
    }
    if (currentUser!.userId != currentUser!.storeOwnerId) {
      return null;
    }
    Map<String, dynamic> returnStatus = await api.editBranch(
        {"branch_name": name, "branch_address": address},
        currentUser!.storeId,
        currentUser!.branchId);
    if (returnStatus["state"] == "success") {
      return MsgInfoCode.actionSuccess;
    } else {
      debugPrint(returnStatus["errors"].toString());
      return MsgInfoCode.actionFail;
    }
  }

  Future<MsgInfoCode?> editEmployeeInfo(
      {required int id,
      required String name,
      required bool selling,
      required bool purchasing,
      required bool managing,
      required bool reporting,
      String? phoneNumber,
      String? gender,
      String? email,
      DateTime? dateOfBirth}) async {
    if (await checkToChangeToOfflineMode()) {
      return null;
    }
    ;
    if (currentUser == null) {
      return null;
    }
    if (currentUser!.storeOwnerId != currentUser!.userId) {
      return null;
    }
    if (name == "") {
      return MsgInfoCode.actionFail;
    }
    Map<String, dynamic> employeeInfoMap = {"user_id": id, "name": name};
    List<String> roles = [];
    if (selling) {
      roles.add("selling");
    }
    if (purchasing) {
      roles.add("purchasing");
    }
    if (managing) {
      roles.add("managing");
    }
    if (reporting) {
      roles.add("reporting");
    }
    employeeInfoMap["role_list"] = roles;
    if (phoneNumber != null) {
      employeeInfoMap["phone"] = phoneNumber;
    }
    if (email != null) {
      employeeInfoMap["email"] = email;
    }
    if (dateOfBirth != null) {
      employeeInfoMap["date_of_birth"] =
          DateFormat("yyyy-MM-dd").format(dateOfBirth);
    }
    Map<String, dynamic> returnStatus = await api.editEmployeeForOwner(
        employeeInfoMap, currentUser!.storeId, currentUser!.branchId);
    debugPrint(returnStatus.toString());
    if (returnStatus["state"] == "success") {
      return MsgInfoCode.actionSuccess;
    } else {
      debugPrint(returnStatus["errors"].toString());
      return MsgInfoCode.actionFail;
    }
  }

  Future<MsgInfoCode> resetPasswordUserForOwner(
      {required int userId, required String newPassword}) async {
    if (currentUser == null) {
      return MsgInfoCode.actionFail;
    }
    if (this.currentUser!.userId != this.currentUser!.storeOwnerId) {
      return MsgInfoCode.actionFail;
    }
    Map<String, dynamic> resetPassMap = {
      "user_id": userId,
      "new_password": newPassword
    };
    Map<String, dynamic> returnResult = (await api.resetPasswordForOwner(
      resetPassMap,
      this.currentUser!.storeId!,
      this.currentUser!.branchId!,
    ));
    if (returnResult["state"] == "success") {
      return MsgInfoCode.actionSuccess;
    } else {
      if (returnResult["errors"] == "Wrong password") {
        return MsgInfoCode.wrongPasswordOrUsername;
      }
      debugPrint(returnResult["errors"].toString());
      return MsgInfoCode.actionFail;
    }
  }

  ///
  ///
  /// Delete method
  ///

  Future<MsgInfoCode?> deleteItem(int? itemId) async {
    if (await checkToChangeToOfflineMode()) {
      return null;
    }
    ;
    if (currentUser == null) {
      return null;
    }
    if (!currentUser!.roles.contains("purchasing")) {
      return null;
    }
    Map<String, dynamic> returnStatus = await api.deleteItem(
        {"item_id": itemId}, currentUser!.storeId, currentUser!.branchId);
    if (returnStatus["state"] == "success") {
      return MsgInfoCode.actionSuccess;
    } else {
      debugPrint(returnStatus["errors"].toString());
      return MsgInfoCode.actionFail;
    }
  }

  Future<MsgInfoCode?> deleteSchedule(int scheduleId) async {
    if (await checkToChangeToOfflineMode()) {
      return null;
    }
    ;
    if (currentUser == null) {
      return null;
    }
    if (!currentUser!.roles.contains("managing")) {
      return null;
    }
    Map<String, dynamic> returnStatus = await api.deleteSchedule(
        {"schedule_id": scheduleId}, currentUser!.storeId, currentUser!.branchId);
    if (returnStatus["state"] == "success") {
      return MsgInfoCode.actionSuccess;
    } else {
      debugPrint(returnStatus["errors"].toString());
      return MsgInfoCode.actionFail;
    }
  }

  Future<MsgInfoCode?> changeStatusAccount(int userId,bool status) async {
    if (await checkToChangeToOfflineMode()) {
      return null;
    }
    if (currentUser == null) {
      return null;
    }
    if (!currentUser!.roles.contains("managing")) {
      return null;
    }
    Map<String, dynamic> returnStatus = await api.changeStatusAccount(
        {"user_id": userId,"status":status?"enable":"disable"}, currentUser!.storeId, currentUser!.branchId);
    if (returnStatus["state"] == "success") {
      return MsgInfoCode.actionSuccess;
    } else {
      debugPrint(returnStatus["errors"].toString());
      return MsgInfoCode.actionFail;
    }
  }

}

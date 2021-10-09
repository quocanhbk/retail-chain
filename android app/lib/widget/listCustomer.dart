import 'package:bkrm/pages/Nav2App.dart';
import 'package:bkrm/pages/managementModule/customerManagement/addNewCustomer.dart';
import 'package:bkrm/services/info/sellingInfo/customerInfo.dart';
import 'package:bkrm/services/services.dart';
import 'package:flutter/material.dart';
import 'package:flutter_barcode_scanner/flutter_barcode_scanner.dart';
import 'package:page_transition/page_transition.dart';
import 'package:permission_handler/permission_handler.dart';

class ListCustomers extends StatefulWidget {
  late _ListCustomersState _state;
  @override
  _ListCustomersState createState() {
    _state = _ListCustomersState();
    return _state;
  }
}

class _ListCustomersState extends State<ListCustomers> {
  BkrmService bkrmService = BkrmService();
  String searchQuery = "";
  TextEditingController controller = TextEditingController();
  List<CustomerInfo>? customersData;
  @override
  void initState() {
    super.initState();
    getCustomerData();
  }

  getCustomerData() async {
    customersData = (await bkrmService.getCustomer()).reversed.toList();
    setState(() {});
  }

  bool isNumeric(String? s) {
    if (s == null) {
      return false;
    }
    return double.tryParse(s) != null;
  }

  @override
  Widget build(BuildContext context) {
    return AlertDialog(
      title: Row(
        mainAxisAlignment: MainAxisAlignment.spaceAround,
        children: [
          Text("Khách Hàng"),
          IconButton(
              icon: Icon(Icons.qr_code),
              onPressed: () async {
                var status = await Permission.camera.status;
                if (status.isPermanentlyDenied ||
                    status.isRestricted ||
                    status.isDenied) {
                  await Permission.camera.request();
                }
                var resultPhone = await FlutterBarcodeScanner.scanBarcode(
                    "#ffffff",
                    "Hủy",
                    true,
                    ScanMode.DEFAULT);
                if(resultPhone=="-1"){
                  return;
                }
                showDialog(
                    context: context,
                    builder: (context) {
                      return AlertDialog(
                        content: Container(
                          height: 50,
                          child: Center(child: CircularProgressIndicator()),
                        ),
                      );
                    });
                if (customersData == null) {
                  Navigator.pop(context);
                  return;
                }
                for (CustomerInfo customer in customersData!) {
                  if (customer.phoneNumber == resultPhone) {
                    if (bkrmService.cart == null) {
                      return;
                    }
                    bkrmService.cart!.customer = customer;
                    bkrmService.requestCart();
                    Navigator.pop(context);
                    Navigator.pop(context);
                    return;
                  }
                }
                Navigator.pop(context);
              }),
          IconButton(
              icon: Icon(Icons.person_add_alt_1_sharp),
              onPressed: () {
                if (searchQuery != "") {
                  if (isNumeric(searchQuery)) {
                    Navigator.push(context,
                        PageTransition(child: AddNewCustomerPage(
                          referPhoneNumber: searchQuery,
                        ), type: pageTransitionType)).then((value) {
                      if (value != null) {
                        if (value) {
                          getCustomerData();
                          setState(() {});
                        }
                      }
                    });
                  } else {
                    Navigator.push(context,
                        PageTransition(child: AddNewCustomerPage(
                          referName: searchQuery,
                        ), type: pageTransitionType)).then((value) {
                      if (value != null) {
                        if (value) {
                          getCustomerData();
                          setState(() {});
                        }
                      }
                    });
                  }
                }else{
                  Navigator.push(context,
                      PageTransition(child: AddNewCustomerPage(
                      ), type: pageTransitionType)).then((value) {
                    if (value != null) {
                      if (value) {
                        getCustomerData();
                        setState(() {});
                      }
                    }
                  });
                }
              })
        ],
      ),
      content: SingleChildScrollView(
        child: Column(mainAxisSize: MainAxisSize.min, children: [
          TextField(
            controller: controller,
            style: TextStyle(fontSize: 14),
            onChanged: (value) {
              searchQuery = value.toLowerCase();
              setState(() {});
            },
            decoration: InputDecoration(
                suffixIcon: IconButton(
                  onPressed: () {
                    controller.clear();
                    searchQuery = "";
                  },
                  icon: Icon(Icons.clear),
                ),
                isDense: true,
                labelText: "Tìm kiếm",
                hintText: "Nhập tên khách hàng hoặc SĐT",
                prefixIcon: Icon(Icons.search),
                border: OutlineInputBorder(
                    borderRadius: BorderRadius.all(Radius.circular(25.0)))),
          ),
          RefreshIndicator(
            onRefresh: () async {
              await this.getCustomerData();
              setState(() {});
              return;
            },
            child: Container(
                child: customersData == null
                    ? Container(
                        height: 250,
                        child: Center(child: CircularProgressIndicator()),
                      )
                    : Container(
                        height: 250,
                        width: MediaQuery.of(context).size.width * 0.75,
                        child: ListView.builder(
                            shrinkWrap: true,
                            itemCount: customersData!.length,
                            itemBuilder: (context, index) {
                              if (searchQuery != "") {
                                if (customersData![index]
                                        .name
                                        .toString()
                                        .toLowerCase()
                                        .contains(searchQuery) ||
                                    customersData![index]
                                        .phoneNumber
                                        .toString()
                                        .toLowerCase()
                                        .contains(searchQuery)) {
                                  return ListTile(
                                    title: Text(customersData![index].name??"Khách hàng lẻ"),
                                    subtitle: Text(
                                        customersData![index].phoneNumber!),
                                    onTap: () {
                                      bkrmService.cart!.customer =
                                          customersData![index];
                                      Navigator.pop(context);
                                    },
                                  );
                                } else {
                                  return Container();
                                }
                              } else {
                                return ListTile(
                                  title:
                                      Text(customersData![index].phoneNumber!),
                                  subtitle: Text(
                                      customersData![index].name != null
                                          ? customersData![index].name!
                                          : "Khách hàng lẻ"),
                                  onTap: () {
                                    debugPrint("customer point:" +
                                        customersData![index]
                                            .customerPoint
                                            .toString());
                                    bkrmService.cart!.customer =
                                        customersData![index];
                                    Navigator.pop(context);
                                  },
                                );
                              }
                            }),
                      )),
          ),
        ]),
      ),
    );
  }
}

import 'package:bkrm/pages/managementModule/customerManagement/addNewCustomer.dart';
import 'package:bkrm/pages/managementModule/customerManagement/customerDetailPage.dart';
import 'package:bkrm/services/info/sellingInfo/customerInfo.dart';
import 'package:bkrm/services/services.dart';
import 'package:bkrm/widget/menuWidget.dart';
import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import 'package:page_transition/page_transition.dart';

import 'package:bkrm/pages/Nav2App.dart';

class ListCustomerPage extends StatefulWidget {
  Function(CustomerInfo)? onTapCustomer;

  ListCustomerPage({Function(CustomerInfo)? ontapCustomer});

  @override
  _ListCustomerPageState createState() => _ListCustomerPageState();
}

class _ListCustomerPageState extends State<ListCustomerPage> {
  GlobalKey<RefreshIndicatorState> _refreshKey = GlobalKey();
  TextEditingController searchController = TextEditingController();
  String searchQuery = "";
  List<CustomerInfo>? customersData;
  late Function refreshFunction;
  @override
  void initState() {
    super.initState();
    refreshFunction = getCustomerData;
    refreshFunction();
  }

  Future<void> getCustomerData() async {
    customersData = (await BkrmService().getCustomer()).reversed.toList();
    setState(() {});
  }

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: () {
        FocusScope.of(context).requestFocus(FocusNode());
      },
      child: Scaffold(
        resizeToAvoidBottomInset: false,
        appBar: AppBar(
          title: Text(
            "Khách hàng",
          ),
          actions: [
            IconButton(
                icon: Icon(Icons.add),
                onPressed: () async {
                  final result = await Navigator.push(context,
                      PageTransition(child: AddNewCustomerPage(), type: pageTransitionType));
                  if (result) {
                    if(_refreshKey.currentState!=null){
                      _refreshKey.currentState!.show();
                    }else{
                      setState(() {
                        getCustomerData();
                      });
                    }
                  }
                })
          ],
        ),
        drawer: ExpansionDrawer(this.context),
        body: Container(
          padding: EdgeInsets.all(8.0),
          child: Container(
            child: Form(
                child: Column(mainAxisSize: MainAxisSize.min, children: [
              TextField(
                controller: searchController,
                style: TextStyle(fontSize: 16),
                onChanged: (value) {
                  searchQuery = value.toLowerCase();
                  setState(() {});
                },
                decoration: InputDecoration(
                    isDense: true,
                    labelText: "Tìm kiếm",
                    hintText: "Nhập tên khách hàng hoặc SĐT",
                    prefixIcon: Icon(Icons.search),
                    border: OutlineInputBorder(
                        borderRadius: BorderRadius.all(Radius.circular(25.0)))),
              ),
              Expanded(
                child: Container(
                    child: customersData == null
                        ? Container(
                            height: 250,
                            child: Center(child: CircularProgressIndicator()),
                          )
                        : (customersData!.isEmpty
                            ? Container(
                                child: Center(
                                  child: Text(
                                    "Không có khách hàng!",
                                    style: TextStyle(
                                        fontSize: 18,
                                        fontWeight: FontWeight.w300,
                                        color: Colors.grey),
                                  ),
                                ),
                              )
                            : RefreshIndicator(
                                key: _refreshKey,
                                onRefresh:
                                    refreshFunction as Future<void> Function(),
                                child: Container(
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
                                              if (customersData![index].name ==
                                                      null ||
                                                  customersData![index].name ==
                                                      null) {
                                                return ListTile(
                                                  title: Text(
                                                      customersData![index]
                                                          .phoneNumber!),
                                                  subtitle:
                                                      Text("Khách hàng lẻ"),
                                                  onTap: () {
                                                    if (widget.onTapCustomer !=
                                                        null) {
                                                      widget.onTapCustomer!(
                                                          customersData![
                                                              index]);
                                                    } else {
                                                      Navigator.push(context,
                                                          PageTransition(child: CustomerDetailPage(
                                                              customersData![
                                                              index]),type: pageTransitionType)).then((value) {
                                                        if (value) {
                                                          _refreshKey
                                                              .currentState!
                                                              .show();
                                                        }
                                                      });
                                                    }
                                                  },
                                                );
                                              } else {
                                                return ListTile(
                                                  title: Text(
                                                      customersData![index]
                                                          .name!),
                                                  subtitle: Text(
                                                      customersData![index]
                                                          .phoneNumber!),
                                                  onTap: () {
                                                    if (widget.onTapCustomer !=
                                                        null) {
                                                      widget.onTapCustomer!(
                                                          customersData![
                                                              index]);
                                                    } else {
                                                      Navigator.push(context,
                                                          PageTransition(child: CustomerDetailPage(
                                                              customersData![
                                                              index]), type: pageTransitionType)).then((value) {
                                                        if (value) {
                                                          _refreshKey
                                                              .currentState!
                                                              .show();
                                                        }
                                                      });
                                                    }
                                                  },
                                                );
                                              }
                                            } else {
                                              return Container();
                                            }
                                          } else {
                                            if (customersData![index].name ==
                                                    null ||
                                                customersData![index].name ==
                                                    null) {
                                              return ListTile(
                                                title: Text(
                                                    customersData![index]
                                                        .phoneNumber!),
                                                subtitle: Text("Khách hàng lẻ"),
                                                onTap: () {
                                                  if (widget.onTapCustomer !=
                                                      null) {
                                                    widget.onTapCustomer!(
                                                        customersData![index]);
                                                  } else {
                                                    Navigator.push(context,
                                                        PageTransition(child: CustomerDetailPage(
                                                            customersData![
                                                            index]),type: pageTransitionType)).then((value) {
                                                      if (value) {
                                                        _refreshKey
                                                            .currentState!
                                                            .show();
                                                      }
                                                    });
                                                  }
                                                },
                                              );
                                            } else {
                                              return ListTile(
                                                title: Text(
                                                    customersData![index]
                                                        .name!),
                                                subtitle: Text(
                                                    customersData![index]
                                                        .phoneNumber!),
                                                onTap: () {
                                                  if (widget.onTapCustomer !=
                                                      null) {
                                                    widget.onTapCustomer!(
                                                        customersData![index]);
                                                  } else {
                                                    Navigator.push(context,
                                                        PageTransition(child: CustomerDetailPage(
                                                            customersData![
                                                            index]), type: pageTransitionType)).then((value) {
                                                      if (value) {
                                                        _refreshKey
                                                            .currentState!
                                                            .show();
                                                      }
                                                    });
                                                  }
                                                },
                                              );
                                            }
                                          }
                                        })),
                              ))),
              ),
            ])),
          ),
        ),
      ),
    );
  }
}

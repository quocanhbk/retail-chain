import 'dart:io';

import 'package:bkrm/pages/humanResourceModule//employee/addNewEmployeeUser.dart';
import 'package:bkrm/pages/humanResourceModule/employee/employeeDetailPage.dart';
import 'package:bkrm/services/info/hrInfo/employeeInfo.dart';
import 'package:bkrm/services/services.dart';
import 'package:bkrm/widget/menuWidget.dart';
import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import 'package:page_transition/page_transition.dart';

import 'package:bkrm/pages/Nav2App.dart';

class ListEmployeePage extends StatefulWidget {
  Function(EmployeeInfo)? onTapCustomer;

  ListEmployeePage({Function(EmployeeInfo)? ontapCustomer});

  @override
  _ListEmployeePageState createState() => _ListEmployeePageState();
}

class _ListEmployeePageState extends State<ListEmployeePage> {
  GlobalKey<RefreshIndicatorState> _refreshKey = GlobalKey();
  GlobalKey<ScaffoldState> _scaffoldKey = GlobalKey();
  TextEditingController searchController = TextEditingController();
  String searchQuery = "";
  List<EmployeeInfo>? employeesData;
  late Function refreshFunction;
  @override
  void initState() {
    super.initState();
    refreshFunction = getSupplierData;
    refreshFunction();
  }

  Future<void> getSupplierData() async {
    employeesData = (await BkrmService().getEmployee()).reversed.toList();
    setState(() {});
  }

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: () {
        FocusScope.of(context).requestFocus(FocusNode());
      },
      child: Scaffold(
        key: _scaffoldKey,
        resizeToAvoidBottomInset: false,
        appBar: AppBar(
          title: Text(
            "Nhân viên",
          ),
          actions: [
            IconButton(
                icon: Icon(Icons.add,color: BkrmService().currentUser!.storeOwnerId!=BkrmService().currentUser!.userId?Colors.grey:Colors.white,),
                onPressed: BkrmService().currentUser!.storeOwnerId!=BkrmService().currentUser!.userId ?null:()async {
                  final result = await Navigator.push(context,
                      PageTransition(child: AddNewEmployeeUserPage(), type: pageTransitionType));
                  if (result != null) {
                    if (result) {
                      if (_refreshKey.currentState != null) {
                        if(_refreshKey.currentState!=null){
                          _refreshKey.currentState!.show();
                        }else{
                          getSupplierData();
                        }

                      } else {
                        setState(() {
                          getSupplierData();
                        });
                      }
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
                    hintText: "Nhập tên nhân viên",
                    prefixIcon: Icon(Icons.search),
                    border: OutlineInputBorder(
                        borderRadius: BorderRadius.all(Radius.circular(25.0)))),
              ),
              Expanded(
                child: Container(
                  child: employeesData == null
                      ? Container(
                          height: 250,
                          child: Center(child: CircularProgressIndicator()),
                        )
                      : (employeesData!.isEmpty
                          ? Container(
                              child: Center(
                                child: Text(
                                  "Không có nhân viên !",
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
                                      itemCount: employeesData!.length,
                                      itemBuilder: (context, index) {
                                        if (searchQuery != "") {
                                          if (employeesData![index]
                                              .name
                                              .toString()
                                              .toLowerCase()
                                              .contains(searchQuery)) {
                                            return ListTile(
                                              leading: employeesData![index].status=="disable"?Icon(Icons.disabled_by_default):null,
                                              title: Text(
                                                employeesData![index].name,style: TextStyle(decoration: employeesData![index].status=="disable"?TextDecoration.lineThrough:TextDecoration.none),),
                                              subtitle: Text(
                                                  employeesData![index].phone ==
                                                          null
                                                      ? ""
                                                      : employeesData![index]
                                                          .phone!),
                                              onTap: () async {
                                                if (widget.onTapCustomer !=
                                                    null) {
                                                  widget.onTapCustomer!(
                                                      employeesData![index]);
                                                } else {
                                                  final result =
                                                      await Navigator.push(
                                                          context,
                                                          PageTransition(child: EmployeeDetailPage(
                                                              employeesData![index]), type: pageTransitionType));
                                                  if (result) {
                                                    if (_refreshKey
                                                            .currentState !=
                                                        null) {
                                                      _refreshKey.currentState!
                                                          .show();
                                                    } else {
                                                      setState(() {
                                                        getSupplierData();
                                                      });
                                                    }
                                                  }
                                                }
                                              },
                                            );
                                          } else {
                                            return Container();
                                          }
                                        } else {
                                          return ListTile(
                                            leading: employeesData![index].status=="disable"?Icon(Icons.disabled_by_default):null,
                                            title: Text(
                                                employeesData![index].name,style: TextStyle(decoration: employeesData![index].status=="disable"?TextDecoration.lineThrough:TextDecoration.none),),
                                            subtitle: Text(employeesData![index]
                                                        .phone ==
                                                    null
                                                ? ""
                                                : employeesData![index].phone!),
                                            onTap: () async {
                                              if (widget.onTapCustomer !=
                                                  null) {
                                                widget.onTapCustomer!(
                                                    employeesData![index]);
                                              } else {
                                                final result =
                                                    await Navigator.push(
                                                        context,
                                                        PageTransition(child: EmployeeDetailPage(
                                                            employeesData![index]), type: pageTransitionType));
                                                if (result) {
                                                  _refreshKey.currentState!
                                                      .show();
                                                }
                                              }
                                            },
                                          );
                                        }
                                      })),
                            )),
                ),
              ),
            ])),
          ),
        ),
      ),
    );
  }
}

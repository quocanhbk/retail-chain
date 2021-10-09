import 'dart:io';

import 'package:bkrm/pages/Nav2App.dart';
import 'package:bkrm/pages/managementModule/currentUserManagement/detailCurrentUserPage.dart';
import 'package:flutter/material.dart';
import 'package:bkrm/services/services.dart';
import 'package:flutter/services.dart';
import 'package:page_transition/page_transition.dart';

class ExpansionExpand {
  bool isExpanded = false;
  Widget header;
  Widget child;
  ExpansionExpand(this.header, this.child, this.isExpanded);
}

class ExpansionDrawer extends StatefulWidget {
  final BuildContext pageContext;

  ExpansionDrawer(this.pageContext);

  @override
  _ExpansionDrawerState createState() => _ExpansionDrawerState();
}

class _ExpansionDrawerState extends State<ExpansionDrawer> {
  List<ExpansionExpand> expansion = [];
  List<Widget> listMenu = [];
  BkrmService bkrmService = BkrmService();

  @override
  initState() {
    super.initState();
    buildExpansionFirstTime();
  }

  buildExpansionFirstTime() {
    if (bkrmService.currentUser!.roles.contains("selling")) {
      ExpansionExpand temp = ExpansionExpand(
          ListTile(
            title: Text("Bán hàng"),
          ),
          Column(
            children: [
/*              ListTile(
                leading: Icon(Icons.monetization_on,color: Colors.blue,),
                title: Text("Bán hàng"),
                onTap: () {
                  if (ModalRoute.of(widget.pageContext)!.settings.name !=
                      Nav2App.posRoute) {
                    Navigator.pop(widget.pageContext);
                    Navigator.pushNamed(widget.pageContext, Nav2App.posRoute);
                  } else {
                    Navigator.pop(widget.pageContext);
                  }
                },
              ),*/
              ListTile(
                leading: Icon(
                  Icons.shopping_cart,
                  color: Colors.blue,
                ),
                title: Text("Giỏ hàng"),
                onTap: () {
                  if (ModalRoute.of(widget.pageContext)!.settings.name !=
                      Nav2App.shoppingCartRoute) {
                    Navigator.pop(widget.pageContext);
                    Navigator.pushNamed(
                        widget.pageContext, Nav2App.shoppingCartRoute);
                  } else {
                    Navigator.pop(widget.pageContext);
                  }
                },
              ),
              BkrmService().networkAvailable
                  ? ListTile(
                      leading: Icon(
                        Icons.list,
                        color: Colors.blue,
                      ),
                      title: Text("Hóa đơn"),
                      onTap: () {
                        if (ModalRoute.of(widget.pageContext)!.settings.name !=
                            Nav2App.invoiceListRoute) {
                          Navigator.pop(widget.pageContext);
                          Navigator.pushNamed(
                              widget.pageContext, Nav2App.invoiceListRoute);
                        } else {
                          Navigator.pop(widget.pageContext);
                        }
                      },
                    )
                  : Container(),
              BkrmService().networkAvailable
                  ? ListTile(
                      leading: Icon(
                        Icons.list_alt_sharp,
                        color: Colors.blue,
                      ),
                      title: Text("Đơn trả"),
                      onTap: () {
                        if (ModalRoute.of(widget.pageContext)!.settings.name !=
                            Nav2App.refundListRoute) {
                          Navigator.pop(widget.pageContext);
                          Navigator.pushNamed(
                              widget.pageContext, Nav2App.refundListRoute);
                        } else {
                          Navigator.pop(widget.pageContext);
                        }
                      },
                    )
                  : Container(),
            ],
          ),
          false);
      expansion.add(temp);
    }
    if (bkrmService.currentUser!.roles.contains("purchasing") &&
        BkrmService().networkAvailable) {
      ExpansionExpand temp = ExpansionExpand(
          ListTile(
            title: Text("Kho hàng"),
          ),
          Column(
            children: [
              ListTile(
                leading: Icon(Icons.inventory, color: Colors.blue),
                title: Text("Kho hàng"),
                onTap: () {
                  if (ModalRoute.of(widget.pageContext)!.settings.name !=
                      Nav2App.inventoryRoute) {
                    Navigator.pop(widget.pageContext);
                    Navigator.pushNamed(
                        widget.pageContext, Nav2App.inventoryRoute);
                  } else {
                    Navigator.pop(widget.pageContext);
                  }
                },
              ),
              ListTile(
                leading: Icon(Icons.add_business, color: Colors.blue),
                title: Text("Nhập hàng"),
                onTap: () {
                  if (ModalRoute.of(widget.pageContext)!.settings.name !=
                      Nav2App.importGoodRoute) {
                    Navigator.pop(widget.pageContext);
                    Navigator.pushNamed(
                        widget.pageContext, Nav2App.importGoodRoute);
                  } else {
                    Navigator.pop(widget.pageContext);
                  }
                },
              ),
              ListTile(
                leading: Icon(Icons.list, color: Colors.blue),
                title: Text("Đơn nhập hàng"),
                onTap: () {
                  if (ModalRoute.of(widget.pageContext)!.settings.name !=
                      Nav2App.listImportInvoice) {
                    Navigator.pop(widget.pageContext);
                    Navigator.pushNamed(
                        widget.pageContext, Nav2App.listImportInvoice);
                  } else {
                    Navigator.pop(widget.pageContext);
                  }
                },
              ),
              ListTile(
                leading: Icon(Icons.list_rounded, color: Colors.blue),
                title: Text("Đơn trả hàng nhập"),
                onTap: () {
                  if (ModalRoute.of(widget.pageContext)!.settings.name !=
                      Nav2App.listReturnPurchaseSheet) {
                    Navigator.pop(widget.pageContext);
                    Navigator.pushNamed(
                        widget.pageContext, Nav2App.listReturnPurchaseSheet);
                  } else {
                    Navigator.pop(widget.pageContext);
                  }
                },
              ),
              ListTile(
                leading: Icon(
                  Icons.list_alt_sharp,
                  color: Colors.blue,
                ),
                title: Text("Nhà cung cấp"),
                onTap: () {
                  if (ModalRoute.of(widget.pageContext)!.settings.name !=
                      Nav2App.viewAllSupplierRoute) {
                    Navigator.pop(widget.pageContext);
                    Navigator.pushNamed(
                        widget.pageContext, Nav2App.viewAllSupplierRoute);
                  } else {
                    Navigator.pop(widget.pageContext);
                  }
                },
              ),
              ListTile(
                leading: Icon(
                  Icons.view_list,
                  color: Colors.blue,
                ),
                title: Text("Danh mục sản phẩm"),
                onTap: () {
                  if (ModalRoute.of(widget.pageContext)!.settings.name !=
                      Nav2App.viewAllCategoryRoute) {
                    Navigator.pop(widget.pageContext);
                    Navigator.pushNamed(
                        widget.pageContext, Nav2App.viewAllCategoryRoute);
                  } else {
                    Navigator.pop(widget.pageContext);
                  }
                },
              ),
            ],
          ),
          false);
      expansion.add(temp);
    }
    if (bkrmService.currentUser!.roles.contains("managing") &&
        BkrmService().networkAvailable) {
      ExpansionExpand temp = ExpansionExpand(
          ListTile(
            title: Text("Nhân sự"),
          ),
          Column(
            children: [
              ListTile(
                leading: Icon(
                  Icons.people,
                  color: Colors.blue,
                ),
                title: Text("Nhân viên"),
                onTap: () {
                  if (ModalRoute.of(widget.pageContext)!.settings.name !=
                      Nav2App.viewAllEmployeeRoute) {
                    Navigator.pop(widget.pageContext);
                    Navigator.pushNamed(
                        widget.pageContext, Nav2App.viewAllEmployeeRoute);
                  } else {
                    Navigator.pop(widget.pageContext);
                  }
                },
              ),
              ListTile(
                leading: Icon(
                  Icons.schedule,
                  color: Colors.blue,
                ),
                title: Text("Ca làm việc"),
                onTap: () {
                  if (ModalRoute.of(widget.pageContext)!.settings.name !=
                      Nav2App.listShiftRoute) {
                    Navigator.pop(widget.pageContext);
                    Navigator.pushNamed(
                        widget.pageContext, Nav2App.listShiftRoute);
                  } else {
                    Navigator.pop(widget.pageContext);
                  }
                },
              ),
            ],
          ),
          false);
      expansion.add(temp);
    }
    if (bkrmService.currentUser!.roles.contains("reporting") &&
        BkrmService().networkAvailable) {
      ExpansionExpand temp = ExpansionExpand(
          ListTile(
            title: Text("Quản lý"),
          ),
          Column(
            children: [
              BkrmService().currentUser!.storeOwnerId ==
                      BkrmService().currentUser!.userId
                  ? ListTile(
                      leading: Icon(
                        Icons.history,
                        color: Colors.blue,
                      ),
                      title: Text("Lịch sử hoạt động"),
                      onTap: () {
                        if (ModalRoute.of(widget.pageContext)!.settings.name !=
                            Nav2App.historyRoute) {
                          Navigator.pop(widget.pageContext);
                          Navigator.pushNamed(
                              widget.pageContext, Nav2App.historyRoute);
                        } else {
                          Navigator.pop(widget.pageContext);
                        }
                      },
                    )
                  : Container(),
              BkrmService().currentUser!.storeOwnerId ==
                  BkrmService().currentUser!.userId
                  ? ListTile(
                leading: Icon(
                  Icons.store_mall_directory_outlined,
                  color: Colors.blue,
                ),
                title: Text("Cửa hàng"),
                onTap: () {
                  if (ModalRoute.of(widget.pageContext)!.settings.name !=
                      Nav2App.detailBranchRoute) {
                    Navigator.pop(widget.pageContext);
                    Navigator.pushNamed(
                        widget.pageContext, Nav2App.detailBranchRoute);
                  } else {
                    Navigator.pop(widget.pageContext);
                  }
                },
              )
                  : Container(),
              ListTile(
                leading: Icon(
                  Icons.people_alt_outlined,
                  color: Colors.blue,
                ),
                title: Text("Khách hàng"),
                onTap: () {
                  if (ModalRoute.of(widget.pageContext)!.settings.name !=
                      Nav2App.viewAllCustomerRoute) {
                    Navigator.pop(widget.pageContext);
                    Navigator.pushNamed(
                        widget.pageContext, Nav2App.viewAllCustomerRoute);
                  } else {
                    Navigator.pop(widget.pageContext);
                  }
                },
              ),
              ListTile(
                leading: Icon(
                  Icons.calculate,
                  color: Colors.blue,
                ),
                title: Text("Thống kê"),
                onTap: () {
                  if (ModalRoute.of(widget.pageContext)!.settings.name !=
                      Nav2App.dashboardRoute) {
                    Navigator.pop(widget.pageContext);
                    Navigator.pushNamed(
                        widget.pageContext, Nav2App.dashboardRoute);
                  } else {
                    Navigator.pop(widget.pageContext);
                  }
                },
              ),
            ],
          ),
          false);
      expansion.add(temp);
    }
  }

  buildDrawer() {
    List<Widget> listMenu = [];
    List<Widget> upperWidget = [];
    upperWidget.add(SizedBox(height: 10,));
    upperWidget.add(Row(
      children: [
        Expanded(
          flex: 1,
          child: Container(
            padding: EdgeInsets.all(8.0),
            alignment: Alignment.centerLeft,
            child: IconButton(
              icon: Icon(
                Icons.logout,
                color: Colors.black,
                size: 40,
              ),
              onPressed: () async {
                showDialog(
                    context: context,
                    builder: (context) {
                      return AlertDialog(
                        title: Text("Xác nhận đăng xuất ?"),
                        actions: [
                          TextButton(
                              onPressed: () {
                                Navigator.pop(context);
                              },
                              child: Text("Không")),
                          TextButton(
                              onPressed: () async {
                                showDialog(
                                    context: context,
                                    builder: (context) {
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
                                await bkrmService.logOut();
                                Navigator.pushNamedAndRemoveUntil(
                                    widget.pageContext,
                                    Nav2App.welcomePage,
                                        (route) => false);
                              },
                              child: Text("Xác nhận"))
                        ],
                      );
                    });
              },
            ),
          ),
        ),
        Expanded(
          flex: 1,
          child: Container(
            padding: EdgeInsets.all(8.0),
            alignment: Alignment.centerRight,
            child: IconButton(
              icon: Icon(
                Icons.power_settings_new,
                color: Colors.black,
                size: 40,
              ),
              onPressed: () async {
                showDialog(
                    context: context,
                    builder: (context) {
                      return AlertDialog(
                        title: Text("Bạn có chắc muốn thoát ứng dụng?"),
                        actions: [
                          TextButton(
                              onPressed: () {
                                Navigator.pop(context);
                              },
                              child: Text("Không")),
                          TextButton(
                              onPressed: () {
                                Navigator.pop(context);
                                SystemChannels.platform
                                    .invokeMethod('SystemNavigator.pop');
                              },
                              child: Text("Có")),
                        ],
                      );
                    });
              },
            ),
          ),
        )
      ],
    ));
    upperWidget.add(InkWell(
      onTap: () {
        Navigator.push(
                context,
                PageTransition(
                    type: pageTransitionType, child: CurrentUserDetailPage()))
            .then((value) {
          if (value != null) {
            if (value) {
              setState(() {});
            }
          }
        });
      },
      child: Container(
        child: CircleAvatar(
          radius: 60,
          foregroundImage: FileImage(File(BkrmService().currentUser!.avatarFile)),
        ),
      ),
    ));
    upperWidget.add(SizedBox(height:5,));
    upperWidget.add(Container(
      child: Center(
        child: Text(
          bkrmService.currentUser!.name,
          style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
        ),
      ),
    ));
    upperWidget.add(SizedBox(height: 10,));
    listMenu.add(
      Container(
        color:
        // Colors.lightBlue,
        Color.fromRGBO(156, 230, 255, 1),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: upperWidget,
        ),
      )
    );
    List<Widget> itemMenu = [];
    List<ExpansionPanel> listExpansion = expansion.map((e) {
      return ExpansionPanelRadio(
          canTapOnHeader: true,
          headerBuilder: (context, isExpanded) {
            return e.header;
          },
          body: e.child,
          value: e);
    }).toList();
    itemMenu.add(ExpansionPanelList.radio(
      children: listExpansion,
      expansionCallback: (index, isExpanded) {
        setState(() {
          expansion[index].isExpanded = !isExpanded;
        });
      },
    ));
    listMenu.add(Expanded(
      child: ListView(
        padding: EdgeInsets.all(0.0),
        shrinkWrap: true,
        children: itemMenu
        ),
    ),
    );
    return listMenu;
  }

  @override
  Widget build(BuildContext context) {
    return Drawer(
      child: Container(color: Colors.white,child: Column(mainAxisSize: MainAxisSize.min,children: buildDrawer())),
    );
  }
}

import 'package:bkrm/pages/managementModule/supplierManagement/addNewSupplier.dart';
import 'package:bkrm/pages/managementModule/supplierManagement/supplierDetailPage.dart';
import 'package:bkrm/services/info/inventoryInfo/supplierInfo.dart';
import 'package:bkrm/services/services.dart';
import 'package:bkrm/widget/menuWidget.dart';
import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import 'package:page_transition/page_transition.dart';

import 'package:bkrm/pages/Nav2App.dart';

class ListSupplierPage extends StatefulWidget {
  Function (SupplierInfo)? onTapCustomer;

  ListSupplierPage({Function (SupplierInfo)? ontapCustomer});

  @override
  _ListSupplierPageState createState() => _ListSupplierPageState();
}

class _ListSupplierPageState extends State<ListSupplierPage> {
  GlobalKey<RefreshIndicatorState> _refreshKey = GlobalKey();

  TextEditingController searchController=TextEditingController();
  String searchQuery="";
  List<SupplierInfo>? suppliersData;
  late Function refreshFunction;
  @override
  void initState() {
    super.initState();
    refreshFunction=getSupplierData;
    refreshFunction();
  }

  Future<void> getSupplierData()async{
    suppliersData = await BkrmService().getSupplier();
    setState(() {});
  }
  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: (){FocusScope.of(context).requestFocus(FocusNode());},
      child: Scaffold(
        resizeToAvoidBottomInset: false,
        appBar: AppBar(title: Text("Nhà cung cấp",),actions: [IconButton(icon: Icon(Icons.add), onPressed: ()async{
          final result = await Navigator.push(context, PageTransition(child: AddNewSupplierPage(), type: pageTransitionType));
          if(result!=null){
            if(result=="created"){
              if(_refreshKey.currentState!=null){
                _refreshKey.currentState!.show();
              }else{
                setState(() {
                  getSupplierData();
                });
              }

            }
          }
        })],),
        drawer: ExpansionDrawer(this.context),
        body: Container(
          padding: EdgeInsets.all(8.0),
          child: Container(
            child: Form(
              child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    TextField(
                      controller: searchController,
                      style: TextStyle(fontSize: 16),
                      onChanged: (value) {
                        searchQuery=value.toLowerCase();
                        setState(() {});
                      },
                      decoration: InputDecoration(
                          isDense: true,
                          labelText: "Tìm kiếm",
                          hintText: "Nhập tên nhà cung cấp hoặc SĐT",
                          prefixIcon: Icon(Icons.search),
                          border: OutlineInputBorder(
                              borderRadius: BorderRadius.all(Radius.circular(25.0)))),
                    ),Expanded(
                      child: Container(
                          child: suppliersData==null?
                          Container(
                            height: 250,
                            child: Center(child: CircularProgressIndicator()),
                          ):
                          RefreshIndicator(
                            key: _refreshKey,
                            onRefresh: refreshFunction as Future<void> Function(),
                            child: Container(
                              child: suppliersData!.isEmpty?Container(
                                child: Center(
                                  child: Text(
                                    "Không có nhà cung cấp !",
                                    style: TextStyle(
                                        fontSize: 18,
                                        fontWeight: FontWeight.w300,
                                        color: Colors.grey),
                                  ),
                                ),
                              ):
                              ListView.builder(shrinkWrap: true,itemCount: suppliersData!.length,itemBuilder:(context,index){
                                if(searchQuery!=""){
                                  if(suppliersData![index].name.toString().toLowerCase().contains(searchQuery)||suppliersData![index].phoneNumber.toString().toLowerCase().contains(searchQuery)){
                                    if(suppliersData![index].name==null||suppliersData![index].name==null){
                                      return ListTile(
                                        title: Text(suppliersData![index].phoneNumber!),
                                        subtitle: Text("Nhà cung cấp lẻ"),
                                        onTap: ()async{
                                          if(widget.onTapCustomer!=null){
                                            widget.onTapCustomer!(suppliersData![index]);
                                          }else{
                                            final result = await Navigator.push(context, PageTransition(child: SupplierDetailPage(suppliersData![index]), type: pageTransitionType));
                                            if(result){
                                              if(_refreshKey.currentState!=null){
                                                _refreshKey.currentState!.show();
                                              }else{
                                                setState(() {
                                                  getSupplierData();
                                                });
                                              }

                                            }
                                          }
                                        },
                                      );
                                    }else{
                                      return ListTile(
                                        title: Text(suppliersData![index].name!),
                                        subtitle: Text(suppliersData![index].phoneNumber!),
                                        onTap: ()async{
                                          if(widget.onTapCustomer!=null){
                                            widget.onTapCustomer!(suppliersData![index]);
                                          }else{
                                            final result = await Navigator.push(context, PageTransition(type: pageTransitionType,child: SupplierDetailPage(suppliersData![index])));
                                            if(result){
                                              if(_refreshKey.currentState!=null){
                                                _refreshKey.currentState!.show();
                                              }else{
                                                setState(() {
                                                  getSupplierData();
                                                });
                                              }

                                            }
                                          }
                                        },
                                      );
                                    }

                                  }else{
                                    return Container();
                                  }
                                }else{
                                  if(suppliersData![index].name==null||suppliersData![index].name==null){
                                    return ListTile(
                                      title: Text(suppliersData![index].phoneNumber!),
                                      subtitle: Text("Nhà cung cấp lẻ"),
                                      onTap: ()async{
                                        if(widget.onTapCustomer!=null){
                                          widget.onTapCustomer!(suppliersData![index]);
                                        }else{
                                          final result = await Navigator.push(context, PageTransition(child: SupplierDetailPage(suppliersData![index]),type: pageTransitionType));
                                          if(result){
                                            if(_refreshKey.currentState!=null){
                                              _refreshKey.currentState!.show();
                                            }else{
                                              setState(() {
                                                getSupplierData();
                                              });
                                            }

                                          }
                                        }
                                      },
                                    );
                                  }else{
                                    return ListTile(
                                      title: Text(suppliersData![index].name!),
                                      subtitle: Text(suppliersData![index].phoneNumber!),
                                      onTap: ()async{
                                        if(widget.onTapCustomer!=null){
                                          widget.onTapCustomer!(suppliersData![index]);
                                        }else{
                                          final result = await Navigator.push(context, PageTransition(child: SupplierDetailPage(suppliersData![index]), type: pageTransitionType));
                                          if(result){
                                            if(_refreshKey!=null){
                                              _refreshKey.currentState!.show();
                                            }else{
                                              setState(() {
                                                getSupplierData();
                                              });
                                            }

                                          }
                                        }
                                      },
                                    );
                                  }
                                }
                              } )
                            ),
                          )
                      ),
                    ),
                  ]
              )
            ),
          ),
        ),
      ),
    );
  }
}

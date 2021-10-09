import 'package:bkrm/pages/inventoryModule/category/addNewCategory.dart';
import 'package:bkrm/pages/inventoryModule/category/categoryDetailPage.dart';
import 'package:bkrm/services/info/inventoryInfo/categoryInfo.dart';
import 'package:bkrm/services/services.dart';
import 'package:bkrm/widget/menuWidget.dart';
import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import 'package:page_transition/page_transition.dart';

import 'package:bkrm/pages/Nav2App.dart';

class ListCategoryPage extends StatefulWidget {
  Function (CategoryInfo)? onTapCustomer;

  ListCategoryPage({Function (CategoryInfo)? ontapCustomer});

  @override
  _ListCategoryPageState createState() => _ListCategoryPageState();
}

class _ListCategoryPageState extends State<ListCategoryPage> {
  GlobalKey<RefreshIndicatorState> _refreshKey = GlobalKey();
  TextEditingController searchController=TextEditingController();
  List<CategoryInfo>? categoriesData;
  late Function refreshFunction;
  @override
  void initState() {
    super.initState();
    refreshFunction=getCategoryData;
    refreshFunction();
  }

  Future<void> getCategoryData()async{
    categoriesData = await BkrmService().getCategory();
    setState(() {});
  }
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      resizeToAvoidBottomInset: true,
      appBar: AppBar(title: Text("Danh sách danh mục",),actions: [IconButton(icon: Icon(Icons.add), onPressed: (){
        Navigator.push(context, PageTransition(
          child: AddNewCategoryPage(), type: pageTransitionType
      )).then((value){
          if(value){
            if(_refreshKey.currentState!=null){
              _refreshKey.currentState!.show();
            }else{
              setState(() {
                getCategoryData();
              });
            }

          }
        });
      })],),
      drawer: ExpansionDrawer(this.context),
      body: Container(
        padding: EdgeInsets.all(8.0),
        child: Container(
            child: categoriesData==null?
            Container(
              height: 250,
              child: Center(child: CircularProgressIndicator()),
            ):
            RefreshIndicator(
              key: _refreshKey,
              onRefresh: refreshFunction as Future<void> Function(),
              child: Container(
                child: categoriesData!.isEmpty?Container(
                  child: Center(
                    child: Text(
                      "Không có danh mục !",
                      style: TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.w300,
                          color: Colors.grey),
                    ),
                  ),
                ):ListView.builder(shrinkWrap: true,itemCount: categoriesData!.length,itemBuilder:(context,index){
                      return ListTile(
                        title: Text(categoriesData![index].name),
                        onTap: (){
                          if(widget.onTapCustomer!=null){
                            widget.onTapCustomer!(categoriesData![index]);
                          }else{
                            Navigator.push(context, PageTransition(child: CategoryDetailPage(categoriesData![index]), type: pageTransitionType)
                            ).then((value){
                              if(value){
                                if(_refreshKey.currentState!=null){
                                  _refreshKey.currentState!.show();
                                }else{
                                  setState(() {
                                    getCategoryData();
                                  });
                                }

                              }
                            });
                          }
                        },
                      );

                } )
              ),
            )
        ),
      ),
    );
  }
}

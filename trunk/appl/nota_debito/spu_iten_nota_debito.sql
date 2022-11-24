-------------------- spu_item_nota_credito ---------------------------------
CREATE PROCEDURE [dbo].[spu_item_nota_debito](
								@ve_operacion varchar(20)
								,@ve_cod_item_nota_debito numeric
								,@ve_cod_nota_debito numeric=NULL
								,@ve_orden numeric=NULL
								,@ve_item varchar(10)=NULL
								,@ve_cod_producto varchar(100)=NULL
								,@ve_nom_producto varchar(100)=NULL
								,@ve_cantidad T_CANTIDAD=NULL
								,@ve_precio T_PRECIO=NULL)

AS
BEGIN
	if (@ve_operacion='INSERT') 
		begin
			insert into item_nota_debito(
				cod_nota_debito
	           	,orden
	           	,item
	           	,cod_producto
	           	,nom_producto
	           	,cantidad
           		,precio
				)
			values(
				@ve_cod_nota_debito
				,@ve_orden
				,@ve_item
				,@ve_cod_producto
				,@ve_nom_producto
				,@ve_cantidad
				,@ve_precio
				) 
		end 
		
	else if (@ve_operacion='UPDATE') 
		begin
			if (@ve_cantidad <> 0) -- si la cantidad es <> de cero hace update, sino borra el ítem
				update item_nota_debito
				set cod_nota_debito		=	@ve_cod_nota_debito
					,orden				=	@ve_orden
					,item				=	@ve_item
					,cod_producto		=	@ve_cod_producto
					,nom_producto		=	@ve_nom_producto
					,cantidad			=	@ve_cantidad
					,precio				=	@ve_precio
					
				where cod_item_nota_debito  =	@ve_cod_item_nota_debito
			else	
				delete  item_nota_debito 
	    		where cod_item_nota_debito  =	@ve_cod_item_nota_debito			
		end	
	else if (@ve_operacion='DELETE') 
		begin
			delete  item_nota_debito 
    		where cod_item_nota_debito  =	@ve_cod_item_nota_debito
		end 
END

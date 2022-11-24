-------------------- spu_item_solicitud_compra ---------------------------------
ALTER  PROCEDURE [dbo].[spu_item_solicitud_compra] (
					 @ve_operacion						varchar(20)
					,@ve_cod_item_solicitud_compra		numeric
					,@ve_cod_solicitud_compra			numeric	= NULL
					,@ve_cod_producto					varchar(30)	= NULL
					,@ve_cantidad_unitaria				T_CANTIDAD	= NULL
					,@ve_cantidad_total					T_CANTIDAD	= NULL
					,@ve_precio_compra					T_PRECIO = NULL
					,@ve_genera_compra					varchar(1)=NULL
					,@ve_cod_empresa					numeric=NULL
					,@ve_arma_compuesto					varchar(1)=NULL
					)
AS
BEGIN
	if (@ve_operacion='INSERT') 
		begin
			insert into item_solicitud_compra
						(cod_solicitud_compra
						,cod_producto
						,cantidad_unitaria				
						,cantidad_total				
						,precio_compra					
						,genera_compra					
						,cod_empresa					
						,ARMA_COMPUESTO
						)
				values	(@ve_cod_solicitud_compra
						,@ve_cod_producto
						,@ve_cantidad_unitaria				
						,@ve_cantidad_total				
						,@ve_precio_compra					
						,@ve_genera_compra					
						,@ve_cod_empresa
						,@ve_arma_compuesto					
						)
		end 
	else if (@ve_operacion='UPDATE') 
		begin
			UPDATE	item_solicitud_compra
			SET	cod_solicitud_compra = @ve_cod_solicitud_compra
				,cod_producto = @ve_cod_producto
				,cantidad_unitaria = @ve_cantidad_unitaria
				,cantidad_total = @ve_cantidad_total
				,precio_compra = @ve_precio_compra
				,genera_compra = @ve_genera_compra
				,cod_empresa = @ve_cod_empresa
				,arma_compuesto = @ve_arma_compuesto
 			WHERE	cod_item_solicitud_compra =	@ve_cod_item_solicitud_compra
		end	
	else if (@ve_operacion='DELETE') 
		begin
			DELETE  item_solicitud_compra
    		WHERE	cod_item_solicitud_compra =	@ve_cod_item_solicitud_compra
		end 
END

ALTER PROCEDURE [dbo].[spu_producto_compuesto]( @ve_operacion varchar(20)
												,@ve_cod_producto_compuesto numeric												
												,@ve_cod_producto varchar(30)=NULL
												,@ve_cod_producto_hijo varchar(30)=NULL												
												,@ve_orden numeric=NULL
												,@ve_cantidad T_CANTIDAD=NULL
												,@ve_genera_compra T_SI_NO=NULL
												,@ve_arma_compuesto T_SI_NO=NULL)
AS
BEGIN
	if (@ve_operacion='INSERT') begin
		insert into producto_compuesto (cod_producto,cod_producto_hijo,orden,cantidad, genera_compra, arma_compuesto)
		values (@ve_cod_producto,@ve_cod_producto_hijo,@ve_orden,@ve_cantidad, @ve_genera_compra, @ve_arma_compuesto)
	end 
	if (@ve_operacion='UPDATE') begin
		update producto_compuesto 
		set		cod_producto		 = @ve_cod_producto,
				cod_producto_hijo	 = @ve_cod_producto_hijo,
				orden				 = @ve_orden,
				cantidad			 = @ve_cantidad,
				genera_compra 		 = @ve_genera_compra,
				arma_compuesto		 = @ve_arma_compuesto	
	    where cod_producto_compuesto = @ve_cod_producto_compuesto
	end
	else if (@ve_operacion='DELETE') begin
		delete producto_compuesto 
    	where cod_producto_compuesto = @ve_cod_producto_compuesto
	end
END
go

----------------------spu_item_faprov---------------------
CREATE PROCEDURE spu_item_faprov
			(@ve_operacion					varchar(20)
			,@ve_cod_item_faprov			numeric
			,@ve_cod_faprov					numeric
			,@ve_cod_doc					numeric  = null
			,@ve_monto_asignado				T_PRECIO = null)

AS
BEGIN
		if (@ve_operacion='UPDATE') 
			begin
				UPDATE item_faprov
				SET		cod_faprov			= @ve_cod_faprov							
					   ,cod_doc			    = @ve_cod_doc
					   ,monto_asignado		= @ve_monto_asignado	

				WHERE cod_item_faprov = @ve_cod_item_faprov 
						
			end
		else if (@ve_operacion='INSERT') 
			begin
				insert into item_faprov
					(cod_faprov
					,cod_doc
					,monto_asignado)
				values 
					(@ve_cod_faprov
					,@ve_cod_doc	
					,@ve_monto_asignado)
			end
		else if (@ve_operacion='DELETE_ALL') 
			begin
				delete item_faprov
    			where cod_faprov = @ve_cod_faprov 
			end
END
go